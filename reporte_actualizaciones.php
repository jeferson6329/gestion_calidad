<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/phpmailer/src/Exception.php';

if (php_sapi_name() !== 'cli') {
    die('Acceso no autorizado.');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

function safe_html($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$hoy_inicio = date('Y-m-d') . ' 00:00:00';
$hoy_fin = date('Y-m-d') . ' 23:59:59';

$sql = "SELECT m.id, m.id_llamada, m.nombre_asesor, m.tipo_monitoreo, c.nombre AS canal, m.ecn, m.ecuf, m.ecc, m.enc, m.nota_final, 
               m.fecha_llamada, m.fecha_monitoreo, m.descripcion, m.aspectos_positivos, m.aspectos_mejorar, m.comentario_agente, 
               m.comentario_refute, m.estado, m.realizado_por, m.asesor_id, m.id_usuario, m.valores_criterios, m.fecha_registro, m.fecha_actualizacion
        FROM monitoreos m
        LEFT JOIN canales c ON m.canal_id = c.id
        WHERE m.estado = 'aprobado' 
          AND m.fecha_actualizacion BETWEEN :hoy_inicio AND :hoy_fin
          AND m.comentario_agente = 'Monitoreo aceptado automáticamente'";

$stmt = $conexion->prepare($sql);
$stmt->execute([':hoy_inicio' => $hoy_inicio, ':hoy_fin' => $hoy_fin]);
$actualizados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($actualizados)) {
    $fecha_hora = date('Y-m-d H:i:s');
    $subject = "Monitoreos aceptados automaticamente - $fecha_hora";

    $message = "<html><body>";
    $message .= "<p>Cordial saludo, líderes de calidad</p>";
    $message .= "<p>A continuación, les envío la información correspondiente a los monitoreos aceptados de manera automática del día de hoy. (" . date('Y-m-d') . "):</p>";
    $message .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; font-family: Arial; font-size: 13px;'>";
    $message .= "<thead style='background-color: #f2f2f2;'>
                    <tr>
                        <!-- <th>ID</th> --> 
                        <th>ID Llamada</th>
                        <th>Nombre del Asesor</th>
                        <th>Tipo de Monitoreo</th>
                        <th>Canal</th>
                        <th>ECN</th>
                        <th>ECUF</th>
                        <th>ECC</th>
                        <th>ENC</th>
                        <th>Nota Final</th>
                        <th>Fecha de la Llamada</th>
                        <th>Fecha del Monitoreo</th>
                        <th>Descripción</th>
                        <th>Aspectos Positivos</th>
                        <th>Aspectos a Mejorar</th>
                        <th>Comentario Agente</th>
                        <th>Comentario Refute</th>
                        <th>Estado</th>
                        <th>Realizado por</th>
                        <th>Asesor ID</th>
                        <th>ID Usuario</th>
                        <th>Valores Criterios</th>
                        <th>Fecha de Registro</th>
                        <th>Hora de Aceptación</th>
                    </tr>
                 </thead>
                 <tbody>";

    foreach ($actualizados as $m) {
        $message .= "<tr>
                        <!-- <td>{$m['id']}</td> -->
                        <td>{$m['id_llamada']}</td>
                        <td>{$m['nombre_asesor']}</td>
                        <td>{$m['tipo_monitoreo']}</td>
                        <td>{$m['canal']}</td>
                        <td>{$m['ecn']}</td>
                        <td>{$m['ecuf']}</td>
                        <td>{$m['ecc']}</td>
                        <td>{$m['enc']}</td>
                        <td>{$m['nota_final']}</td>
                        <td>{$m['fecha_llamada']}</td>
                        <td>{$m['fecha_monitoreo']}</td>
                        <td>" . nl2br(safe_html($m['descripcion'])) . "</td>
                        <td>" . nl2br(safe_html($m['aspectos_positivos'])) . "</td>
                        <td>" . nl2br(safe_html($m['aspectos_mejorar'])) . "</td>
                        <td>" . nl2br(safe_html($m['comentario_agente'])) . "</td>
                        <td>" . nl2br(safe_html($m['comentario_refute'])) . "</td>
                        <td>{$m['estado']}</td>
                        <td>{$m['realizado_por']}</td>
                        <td>{$m['asesor_id']}</td>
                        <td>{$m['id_usuario']}</td>
                        <td>" . nl2br(safe_html($m['valores_criterios'])) . "</td>
                        <td>{$m['fecha_registro']}</td>
                        <td>{$m['fecha_actualizacion']}</td>
                    </tr>";
    }

    $message .= "</tbody></table>";
    $message .= "<br><p>Cordialmente,<br></p><br><br>";

    $message .= '
    <table style="font-family: Arial, sans-serif; font-size:14px; color:#333; line-height:1.4;">
      <tr>
        <td style="vertical-align: middle; padding-right:10px;">
          <img src="http://sdm.jldesarrolloweb.com/favicon.ico" alt="JL Desarrollo Web" width="90" height="90" style="display:block;">
        </td>
        <td style="vertical-align: middle;">
          <strong>JL Desarrollo Web</strong><br>
          <small style="color:#666;">
            Este mensaje fue enviado desde una cuenta exclusiva para notificaciones.<br>
            No responda a este correo.<br>
            🌐 <a href="https://www.sdm.jldesarrolloweb.com" style="color:#1a73e8; text-decoration:none;">www.sdm.jldesarrolloweb.com</a>
          </small>
        </td>
      </tr>
    </table>
    <hr style="border:none; border-top:1px solid #ccc; margin:20px 0;">';

    $message .= "</body></html>";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'sdm.jldesarrolloweb.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'no-reply@sdm.jldesarrolloweb.com';
        $mail->Password = 'Movilidad2025*';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('no-reply@sdm.jldesarrolloweb.com', 'Gestion de Calidad SDM');
        $mail->addAddress('jeferson.lugo1012@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        echo "[" . date('Y-m-d H:i:s') . "] ✅ Correo enviado con " . count($actualizados) . " registros.\n";
    } catch (Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] ❌ Error al enviar el correo: {$mail->ErrorInfo}\n";
    }
} else {
    echo "[" . date('Y-m-d H:i:s') . "] No hubo monitoreos aceptados automáticamente hoy.\n";
}
