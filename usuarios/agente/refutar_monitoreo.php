<?php

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
include_once '../agente/bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/home/jldesarr/sdm.jldesarrolloweb.com/phpmailer/src/PHPMailer.php';
require '/home/jldesarr/sdm.jldesarrolloweb.com/phpmailer/src/SMTP.php';
require '/home/jldesarr/sdm.jldesarrolloweb.com/phpmailer/src/Exception.php';

// Datos recibidos por POST
$id = $_POST['id'];
$comentario = $_POST['comentario'];

// Actualizar estado del monitoreo
$consulta = "UPDATE monitoreos SET estado='en revision', comentario_agente=? WHERE id=?";
$resultado = $conexion->prepare($consulta);
$resultado->execute([$comentario, $id]);

if ($resultado->rowCount() > 0) {

    // Obtener información del monitoreo y usuarios
    $sql = "SELECT 
                m.id AS monitoreo_id,
                m.id_llamada,
                m.nombre_asesor,
                m.tipo_monitoreo,
                m.canal_id,
                m.criterio1,
                m.criterio2,
                m.criterio3,
                m.criterio4,
                m.nota_final,
                m.fecha_llamada,
                m.fecha_monitoreo,
                m.realizado_por AS nombre_lider,
                p_asesor.correo AS correo_asesor,
                p_supervisor.nombre AS nombre_supervisor,
                p_supervisor.correo AS correo_supervisor,
                p_lider.correo AS correo_lider
            FROM monitoreos m
            LEFT JOIN personas p_asesor ON p_asesor.id = m.asesor_id
            LEFT JOIN personas p_lider ON p_lider.nombre = m.realizado_por
            LEFT JOIN personas p_supervisor ON p_supervisor.rol = 'supervisor'
            WHERE m.id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        echo json_encode(['success' => false, 'error' => 'No se encontraron datos del monitoreo.']);
        exit;
    }

    // Obtener nombre del canal
    $canal_nombre = 'Desconocido';
    $stmtCanal = $conexion->prepare("SELECT nombre FROM canales WHERE id = ?");
    $stmtCanal->execute([$datos['canal_id']]);
    $rowCanal = $stmtCanal->fetch(PDO::FETCH_ASSOC);
    if ($rowCanal) {
        $canal_nombre = $rowCanal['nombre'];
    }

    // Crear tabla HTML con los datos del monitoreo
    $tabla_html = "
    <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;'>
        <thead>
            <tr style='background:#f2f2f2;'>
                <th>Id llamada</th>
                <th>Nombre Asesor</th>
                <th>Tipo de monitoreo</th>
                <th>Canal</th>
                <th>ECN</th>
                <th>ECUF</th>
                <th>ECC</th>
                <th>ENC</th>
                <th>Nota final</th>
                <th>Fecha llamada</th>
                <th>Fecha monitoreo</th>
                <th>Realizado por</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$datos['id_llamada']}</td>
                <td>{$datos['nombre_asesor']}</td>
                <td>{$datos['tipo_monitoreo']}</td>
                <td>$canal_nombre</td>
                <td>{$datos['criterio1']}</td>
                <td>{$datos['criterio2']}</td>
                <td>{$datos['criterio3']}</td>
                <td>{$datos['criterio4']}</td>
                <td>{$datos['nota_final']}</td>
                <td>{$datos['fecha_llamada']}</td>
                <td>{$datos['fecha_monitoreo']}</td>
                <td>{$datos['nombre_lider']}</td>
            </tr>
        </tbody>
    </table>
    ";

    // Cuerpo del correo
    $message = "
        <p>Cordial saludo, <strong>{$datos['nombre_supervisor']}</strong>:</p>

        <p>Te informamos que el agente <strong>{$datos['nombre_asesor']}</strong> desea refutar el monitoreo con el ID de llamada <strong>#{$datos['id_llamada']}</strong>, realizado por el líder de calidad <strong>{$datos['nombre_lider']}</strong>, correspondiente al monitoreo realizado el día <strong>{$datos['fecha_monitoreo']}</strong>.</p>

        $tabla_html

        <p>Te invitamos a ingresar a <a href='https://sdm.jldesarrolloweb.com' target='_blank'>sdm.jldesarrolloweb.com</a> utilizando tus credenciales para ver más detalles.</p>

        <p>Recuerda que debes dejar un comentario: si aceptas la refutación, se enviará la información a <strong>{$datos['nombre_lider']}</strong>; si no se acepta, la información se enviará a <strong>{$datos['nombre_asesor']}</strong>.</p>

        <br><p>Cordialmente,<br></p><br><br>

        <table style='font-family: Arial, sans-serif; font-size:14px; color:#333; line-height:1.4;'>
          <tr>
            <td style='vertical-align: middle; padding-right:10px;'>
              <img src='http://sdm.jldesarrolloweb.com/favicon.ico' alt='JL Desarrollo Web' width='90' height='90' style='display:block;'>
            </td>
            <td style='vertical-align: middle;'>
              <strong>JL Desarrollo Web</strong><br>
              <small style='color:#666;'>
                Este mensaje fue enviado desde una cuenta exclusiva para notificaciones.<br>
                No responda a este correo.<br>
                🌐 <a href='https://www.sdm.jldesarrolloweb.com' style='color:#1a73e8; text-decoration:none;'>www.sdm.jldesarrolloweb.com</a>
              </small>
            </td>
          </tr>
        </table>
        <hr style='border:none; border-top:1px solid #ccc; margin:20px 0;'>
    ";

    // Enviar correo
    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8'; // <- esto es clave
        $mail->isSMTP();
        $mail->Host       = 'sdm.jldesarrolloweb.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@sdm.jldesarrolloweb.com';
        $mail->Password   = 'Movilidad2025*'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('no-reply@sdm.jldesarrolloweb.com', 'Gestión de Calidad SDM');
        $mail->addAddress($datos['correo_supervisor'], $datos['nombre_supervisor']);

        $mail->isHTML(true);
        $mail->Subject = "Notificación de refutación de monitoreo #{$datos['id_llamada']}";
        $mail->Body    = $message;
        $mail->AltBody = strip_tags(str_replace("</p>", "\n\n", $message));

        $mail->send();

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => "Error al enviar correo: {$mail->ErrorInfo}"]);
    }

} else {
    echo json_encode([
        'success' => false,
        'error' => 'No se actualizó ningún registro. ID recibido: ' . $id,
        'sql_error' => $resultado->errorInfo()
    ]);
}

$conexion = null;
