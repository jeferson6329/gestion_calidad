<?php
// filepath: bd/guardar_reevaluacion.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../../phpmailer/src/SMTP.php';
require __DIR__ . '/../../../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    include_once __DIR__ . '/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // POST
    $id_monitoreo     = isset($_POST['id_monitoreo']) ? (int) $_POST['id_monitoreo'] : 0;
    $descripcion      = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $aspectos_positivos = isset($_POST['aspectos_positivos']) ? trim($_POST['aspectos_positivos']) : '';
    $aspectos_mejorar = isset($_POST['aspectos_mejorar']) ? trim($_POST['aspectos_mejorar']) : '';
    $comentario_refute= isset($_POST['comentario_refute']) ? trim($_POST['comentario_refute']) : '';
    $fecha_llamada    = isset($_POST['fecha_llamada']) ? $_POST['fecha_llamada'] : null;
    $fecha_monitoreo  = isset($_POST['fecha_monitoreo']) ? $_POST['fecha_monitoreo'] : null;

    if ($id_monitoreo <= 0) {
        throw new Exception('ID de monitoreo inválido.');
    }

    // ================== DATOS DE MONITOREO, ASESOR Y SUPERVISOR ==================
    $stmt = $conexion->prepare("
        SELECT m.id_llamada, m.asesor_id, m.realizado_por,
               a.nombre AS nombre_asesor, a.correo AS correo_asesor, a.supervisor_id,
               s.nombre AS nombre_supervisor, s.correo AS correo_supervisor
        FROM monitoreos m
        JOIN personas a ON m.asesor_id = a.id
        LEFT JOIN personas s ON a.supervisor_id = s.id
        WHERE m.id = ?
    ");
    $stmt->execute([$id_monitoreo]);
    $monData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$monData) {
        throw new Exception("No se encontró información del monitoreo.");
    }

    $id_llamada       = $monData['id_llamada'];
    $asesor_id        = $monData['asesor_id'];
    $nombre_asesor    = $monData['nombre_asesor'];
    $correo_asesor    = $monData['correo_asesor'];
    $nombre_supervisor= $monData['nombre_supervisor'];
    $correo_supervisor= $monData['correo_supervisor'];
    $realizado_por    = $monData['realizado_por']; // líder de calidad

    // ================== CÁLCULO DE NOTA (igual que antes) ==================
    $stmt = $conexion->prepare("SELECT canal_id FROM monitoreos WHERE id = ?");
    $stmt->execute([$id_monitoreo]);
    $canal_id = $stmt->fetchColumn();
    if (!$canal_id) throw new Exception('No se encontró el canal del monitoreo.');

    $stmt = $conexion->prepare("
        SELECT c.id, c.nombre, ccp.peso
        FROM criterios c
        INNER JOIN canal_criterio_peso ccp ON c.id = ccp.criterio_id
        WHERE ccp.canal_id = ?
        ORDER BY c.id
    ");
    $stmt->execute([$canal_id]);
    $criterios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pesos = [];
    $ordenCritIds = [];
    foreach ($criterios as $c) {
        $ordenCritIds[] = (int)$c['id'];
        $pesos[(int)$c['id']] = floatval($c['peso']);
    }
    $map = ['criterio1'=>null,'criterio2'=>null,'criterio3'=>null,'criterio4'=>null];
    foreach ($criterios as $c) {
        $nombre = strtoupper($c['nombre']); $idc=(int)$c['id'];
        if (strpos($nombre,'ECN')!==false && $map['criterio1']===null) $map['criterio1']=$idc;
        if (strpos($nombre,'ECUF')!==false&& $map['criterio2']===null) $map['criterio2']=$idc;
        if (strpos($nombre,'ECC')!==false && $map['criterio3']===null) $map['criterio3']=$idc;
        if (strpos($nombre,'ENC')!==false && $map['criterio4']===null) $map['criterio4']=$idc;
    }
    foreach ($ordenCritIds as $cid) {
        foreach (['criterio1','criterio2','criterio3','criterio4'] as $key) {
            if ($map[$key]===null){$map[$key]=$cid; break;}
        }
    }
    $stmt = $conexion->prepare("
        SELECT mi.item_id, i.criterio_id, mi.cumple AS db_cumple, COALESCE(civ.valor,0) AS valor
        FROM monitoreo_items mi
        JOIN items i ON mi.item_id=i.id
        LEFT JOIN canal_item_valor civ ON civ.canal_id=? AND civ.item_id=i.id
        WHERE mi.monitoreo_id=?
    ");
    $stmt->execute([$canal_id,$id_monitoreo]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $sumTotal=[]; $sumCumplido=[];
    $postCumple = isset($_POST['cumple'])&&is_array($_POST['cumple'])?$_POST['cumple']:[];
    foreach($items as $it){
        $item_id=(int)$it['item_id']; $crit=(int)$it['criterio_id'];
        $valor=(float)$it['valor']; $db_cumple=(int)$it['db_cumple'];
        $cumple=isset($postCumple[$item_id])?(int)$postCumple[$item_id]:$db_cumple;
        if(!isset($sumTotal[$crit])) $sumTotal[$crit]=0.0;
        if(!isset($sumCumplido[$crit])) $sumCumplido[$crit]=0.0;
        $sumTotal[$crit]+=$valor;
        if($cumple===1){$sumCumplido[$crit]+=$valor;}
    }
    $criterioPercent=[]; $nota_final=0.0;
    foreach($pesos as $critId=>$peso){
        $totalPos=$sumTotal[$critId]??0.0; $cumpl=$sumCumplido[$critId]??0.0;
        $porc=$totalPos>0?($cumpl/$totalPos)*100.0:0.0;
        if($map['criterio3']===$critId){
            $huboNo=false;
            foreach($items as $it){
                if((int)$it['criterio_id']===$critId){
                    $item_id=(int)$it['item_id'];
                    $cumple=isset($postCumple[$item_id])?(int)$postCumple[$item_id]:(int)$it['db_cumple'];
                    if($cumple===0){$huboNo=true;break;}
                }
            }
            if($huboNo){$porc=0.0;}
        }
        $criterioPercent[$critId]=$porc;
        $nota_final+=$porc*$peso;
    }
    $val_c1=isset($map['criterio1'],$criterioPercent[$map['criterio1']])?round($criterioPercent[$map['criterio1']],2):0.0;
    $val_c2=isset($map['criterio2'],$criterioPercent[$map['criterio2']])?round($criterioPercent[$map['criterio2']],2):0.0;
    $val_c3=isset($map['criterio3'],$criterioPercent[$map['criterio3']])?round($criterioPercent[$map['criterio3']],2):0.0;
    $val_c4=isset($map['criterio4'],$criterioPercent[$map['criterio4']])?round($criterioPercent[$map['criterio4']],2):0.0;
    $nota_final=round($nota_final,2);

    // ================== ACTUALIZAR DB ==================
    $conexion->beginTransaction();
    $stmt = $conexion->prepare("
        UPDATE monitoreos SET 
            descripcion=?, aspectos_positivos=?, aspectos_mejorar=?, comentario_refute=?,
            estado=?, fecha_llamada=?, fecha_monitoreo=?, 
            criterio1=?, criterio2=?, criterio3=?, criterio4=?, nota_final=?
        WHERE id=?
    ");
    $estado="reevaluado";
    $stmt->execute([
        $descripcion,$aspectos_positivos,$aspectos_mejorar,$comentario_refute,
        $estado,$fecha_llamada,$fecha_monitoreo,
        $val_c1,$val_c2,$val_c3,$val_c4,$nota_final,$id_monitoreo
    ]);
    if(!empty($postCumple)){
        $stmt_item=$conexion->prepare("UPDATE monitoreo_items SET cumple=? WHERE monitoreo_id=? AND item_id=?");
        foreach($postCumple as $item_id=>$valor){
            $stmt_item->execute([(int)$valor===1?1:0,$id_monitoreo,(int)$item_id]);
        }
    }
    $conexion->commit();

    // ================== ENVIAR CORREO ==================
    if(!empty($correo_asesor) || !empty($correo_supervisor)){
        $mail = new PHPMailer(true);
        try{
            date_default_timezone_set('America/Bogota');
            $mail->CharSet='UTF-8';
            $mail->isSMTP();
            $mail->Host='sdm.jldesarrolloweb.com';
            $mail->SMTPAuth=true;
            $mail->Username='no-reply@sdm.jldesarrolloweb.com';
            $mail->Password='Movilidad2025*';
            $mail->SMTPSecure=PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port=465;
            $mail->setFrom('no-reply@sdm.jldesarrolloweb.com','Gestión de Calidad SDM');

            if(!empty($correo_asesor)) $mail->addAddress($correo_asesor,$nombre_asesor);
            if(!empty($correo_supervisor)) $mail->addAddress($correo_supervisor,$nombre_supervisor);

            $mail->isHTML(true);
            $mail->Subject="Notificación de reevaluación de monitoreo - {$id_llamada}";
            $message="
                <p>Cordial saludo, <strong>{$nombre_supervisor}</strong> y <strong>{$nombre_asesor}</strong>:</p>
                <p>El líder de calidad <strong>{$realizado_por}</strong> ha revisado la reevaluación correspondiente para el monitoreo con ID de llamada <strong>{$id_llamada}</strong>.</p>
                <p>Comentario del líder de calidad:</p>
                <blockquote style='border-left:4px solid #ccc;padding-left:10px;color:#555;'>{$comentario_refute}</blockquote>
                <p>Te invitamos a ingresar a <a href='https://sdm.jldesarrolloweb.com' target='_blank'>sdm.jldesarrolloweb.com</a> para revisar el resultado.</p>
                <br>
                <p>Cordialmente,</p>
                <table style='font-family:Arial,sans-serif;font-size:14px;color:#333;line-height:1.4;'>
                  <tr>
                    <td style='vertical-align:middle;padding-right:10px;'>
                      <img src='http://sdm.jldesarrolloweb.com/favicon.ico' alt='JL Desarrollo Web' width='90' height='90'>
                    </td>
                    <td style='vertical-align:middle;'>
                      <strong>JL Desarrollo Web</strong><br>
                      <small style='color:#666;'>
                        Este mensaje fue enviado desde una cuenta exclusiva para notificaciones.<br>
                        No responda a este correo.<br>
                        🌐 <a href='https://www.sdm.jldesarrolloweb.com' style='color:#1a73e8;text-decoration:none;'>www.sdm.jldesarrolloweb.com</a>
                      </small>
                    </td>
                  </tr>
                </table>
            ";
            $mail->Body=$message;
            $mail->AltBody=strip_tags(str_replace("</p>","\n\n",$message));
            $mail->send();
        }catch(Exception $e){
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
        }
    }

    echo json_encode([
        'success'=>true,
        'message'=>'Reevaluación guardada exitosamente.',
        'redirect'=>'/usuarios/lider_calidad/monitoreos.php',
        'debug'=>[
            'criterio1'=>$val_c1,'criterio2'=>$val_c2,
            'criterio3'=>$val_c3,'criterio4'=>$val_c4,
            'nota_final'=>$nota_final
        ]
    ]);
    exit;

}catch(Exception $e){
    if(isset($conexion)&&$conexion->inTransaction()){ $conexion->rollBack(); }
    echo json_encode(['success'=>false,'message'=>'Error al guardar la reevaluación: '.$e->getMessage()]);
    exit;
}
