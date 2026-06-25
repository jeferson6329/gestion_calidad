<?php
include_once 'bd/conexion.php';

require __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../phpmailer/src/SMTP.php';
require __DIR__ . '/../../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id_monitoreo = $_POST['id_monitoreo'] ?? null;
$comentario_supervisor = $_POST['comentario_supervisor'] ?? null;

if (!$id_monitoreo || !$comentario_supervisor) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
    exit;
}

// Obtener datos del monitoreo
$sql_datos = "SELECT m.id, m.id_llamada, m.asesor_id, p.correo, CONCAT(p.nombre, ' ', p.apellido) AS nombre_asesor, p.supervisor 
              FROM monitoreos m
              JOIN personas p ON m.asesor_id = p.id
              WHERE m.id = ?";
$stmt = $conexion->prepare($sql_datos);
$stmt->execute([$id_monitoreo]);
$monitoreo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$monitoreo || empty($monitoreo['correo'])) {
    echo json_encode(['success' => false, 'message' => 'No se encontró asesor o correo']);
    exit;
}

// Actualizar estado y comentario
$sql_update = "UPDATE monitoreos SET estado = 'pendiente', comentario_supervisor = ? WHERE id = ?";
$stmt_update = $conexion->prepare($sql_update);
$exito = $stmt_update->execute([$comentario_supervisor, $id_monitoreo]);

if (!$exito) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar monitoreo']);
    exit;
}

// Enviar correo
try {
    date_default_timezone_set('America/Bogota');
    $fecha_hora = date('Y-m-d H:i:s');

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host       = 'sdm.jldesarrolloweb.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@sdm.jldesarrolloweb.com';
    $mail->Password   = 'Movilidad2025*';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('no-reply@sdm.jldesarrolloweb.com', 'Gestión de Calidad SDM');
    $mail->addAddress($monitoreo['correo'], $monitoreo['nombre_asesor']);
    $mail->isHTML(true);
    $mail->Subject = "Resultado de revisión de refute - {$monitoreo['id_llamada']}";

    $mail->Body = "
        <p>Cordial saludo, <strong>{$monitoreo['nombre_asesor']}</strong>:</p>
        <p>Te informamos que tu supervisor ha revisado tu refute sobre el monitoreo <strong>{$monitoreo['id_llamada']}</strong> y <strong>no aceptó</strong> el motivo del refute sobre tu gestión. El monitoreo nuevamente quedará en estado <strong>\"pendiente\"</strong>.</p>
        <p>Comentario del supervisor: <em>{$comentario_supervisor}</em></p>
        <p>Te invitamos a ingresar a <a href='https://sdm.jldesarrolloweb.com' target='_blank'>sdm.jldesarrolloweb.com</a> utilizando tus credenciales para revisar el resultado.</p>
        <p><strong>Recuerda que cuentas con un plazo de 24 horas laborales desde que realizaron el monitoreo</strong> para aceptar o refutar el monitoreo. Transcurrido este tiempo, se dará por aceptado de forma automática.</p>
        <br>
        <p>Cordialmente,</p>
        <br><br>
        <table style='font-family: Arial, sans-serif; font-size:14px; color:#333; line-height:1.4;'>
            <tr>
                <td style='padding-right:10px;'><img src='https://sdm.jldesarrolloweb.com/favicon.ico' alt='JL Desarrollo Web' width='90'></td>
                <td>
                    <strong>JL Desarrollo Web</strong><br>
                    <small style='color:#666;'>Este mensaje fue enviado desde una cuenta exclusiva para notificaciones.<br>No responda a este correo.<br>
                    🌐 <a href='https://www.sdm.jldesarrolloweb.com'>www.sdm.jldesarrolloweb.com</a></small>
                </td>
            </tr>
        </table>
    ";

    $mail->AltBody = strip_tags($mail->Body);
    $mail->send();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error al enviar correo: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Error al enviar correo']);
}
