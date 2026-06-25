<?php
session_start();

include_once "bd/conexion.php";
require __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../phpmailer/src/SMTP.php';
require __DIR__ . '/../../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id_monitoreo = $_POST['id_monitoreo'] ?? null;
$comentario_supervisor = $_POST['comentario_supervisor'] ?? '';

if (!$id_monitoreo) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
    exit;
}

// Obtener nombre del supervisor desde la sesión y tabla personas
$supervisor_id = $_SESSION['s_id'] ?? null;
$nombre_supervisor = "Supervisor";

if ($supervisor_id) {
    $stmt_supervisor = $conexion->prepare("SELECT nombre, apellido FROM personas WHERE id = ?");
    $stmt_supervisor->execute([$supervisor_id]);
    $supervisor_data = $stmt_supervisor->fetch(PDO::FETCH_ASSOC);
    if ($supervisor_data) {
        $nombre_supervisor = $supervisor_data['nombre'] . ' ' . $supervisor_data['apellido'];
    }
}

// Actualizar monitoreo
$sql = "UPDATE monitoreos SET estado = 'refutado', comentario_supervisor = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);
$exito = $stmt->execute([$comentario_supervisor, $id_monitoreo]);

if (!$exito) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar monitoreo']);
    exit;
}

// Obtener información del monitoreo, agente y líder
$stmt = $conexion->prepare("
    SELECT m.id_llamada, m.realizado_por, m.nombre_asesor, 
           p1.correo AS correo_agente, p1.nombre AS nombre_agente,
           p2.correo AS correo_lider, p2.nombre AS nombre_lider
    FROM monitoreos m
    LEFT JOIN personas p1 ON m.asesor_id = p1.id
    LEFT JOIN personas p2 ON m.id_usuario = p2.id
    WHERE m.id = ?
");
$stmt->execute([$id_monitoreo]);
$monitoreo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$monitoreo || empty($monitoreo['correo_lider']) || empty($monitoreo['correo_agente'])) {
    echo json_encode(['success' => false, 'message' => 'No se encontró correo válido de líder o agente']);
    exit;
}

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
    date_default_timezone_set('America/Bogota');

    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host       = 'sdm.jldesarrolloweb.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@sdm.jldesarrolloweb.com';
    $mail->Password   = 'Movilidad2025*';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('no-reply@sdm.jldesarrolloweb.com', 'Gestión de Calidad SDM');
    $mail->addAddress($monitoreo['correo_lider'], $monitoreo['nombre_lider']);
    $mail->addCC($monitoreo['correo_agente'], $monitoreo['nombre_agente']);
    $mail->isHTML(true);

    $mail->Subject = "Monitoreo Refutado con Anotación - ID Llamada {$monitoreo['id_llamada']}";

    $message = "
        <p>Cordial saludo, <strong>{$monitoreo['nombre_lider']}</strong>:</p>
        <p>El supervisor <strong>{$nombre_supervisor}</strong> ha revisado el refute realizado por el agente <strong>{$monitoreo['nombre_asesor']}</strong> correspondiente al monitoreo con ID de llamada <strong>{$monitoreo['id_llamada']}</strong>.</p>
        <p>Después de la validación, el monitoreo fue marcado como <strong>refutado</strong> y se agregó la siguiente anotación:</p>
        <p><strong>Comentario del supervisor:</strong><br><em>{$comentario_supervisor}</em></p>
        <p>Te invitamos a ingresar a <a href='https://sdm.jldesarrolloweb.com' target='_blank'>sdm.jldesarrolloweb.com</a> para revisar el resultado.</p>
        <p><strong>Recuerda que el monitoreo permanecerá en estado refutado hasta que se resuelva.</strong></p>
        <br>
        <p>Cordialmente,</p><br><br>
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

    $mail->Body = $message;
    $mail->AltBody = strip_tags($message);

    $mail->send();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Error al enviar correo: {$mail->ErrorInfo}");
    echo json_encode(['success' => false, 'message' => 'Error al enviar correo: ' . $mail->ErrorInfo]);
}
