<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include_once 'bd/conexion.php';

// PHPMailer
require __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../phpmailer/src/SMTP.php';
require __DIR__ . '/../../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Recibir datos del formulario
$asesor_id        = $_POST['asesor_id'] ?? null;
$id_llamada       = $_POST['id_llamada'] ?? null;
$tipo_monitoreo   = $_POST['tipo_monitoreo'] ?? null;
$canal            = $_POST['canal'] ?? null;

$nivel_tip1_id    = $_POST['nivel_tipificacion'] ?? null;
$nivel_tip2_id    = $_POST['nivel_tipificacion2'] ?? null;
$nivel_tip1       = null;
$nivel_tip2       = null;

$nota_final       = $_POST['valor_total'] ?? 0;
$fecha_llamada    = $_POST['fecha_llamada'] ?? null;
$fecha_monitoreo  = $_POST['fecha_monitoreo'] ?? null;
$descripcion      = $_POST['descripcion'] ?? '';
$aspectos_pos     = $_POST['aspectos_positivos'] ?? '';
$aspectos_mejorar = $_POST['aspectos_mejorar'] ?? '';
$realizado_por    = $_POST['realizado_por'] ?? '';
$id_usuario       = $_POST['id_usuario'] ?? null;
$estado           = 'pendiente';

// Criterios (4)
$criterios_post = $_POST['criterios'] ?? [];
$crit1 = isset($criterios_post[1]) ? floatval($criterios_post[1]) : 0;
$crit2 = isset($criterios_post[2]) ? floatval($criterios_post[2]) : 0;
$crit3 = isset($criterios_post[3]) ? floatval($criterios_post[3]) : 0;
$crit4 = isset($criterios_post[4]) ? floatval($criterios_post[4]) : 0;

// Obtener nombre y correo del asesor
$stmt = $conexion->prepare("SELECT CONCAT(nombre,' ',apellido) AS nombre_asesor, correo FROM personas WHERE id = ?");
$stmt->execute([$asesor_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre_asesor = $row['nombre_asesor'] ?? '';
$correo_asesor = $row['correo'] ?? '';

// Obtener texto de nivel 1
if ($nivel_tip1_id) {
    $stmt = $conexion->prepare("SELECT texto FROM nivel_tipificacion1 WHERE id = ?");
    $stmt->execute([$nivel_tip1_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nivel_tip1 = $row['texto'] ?? null;
}

// Obtener texto de nivel 2
if ($nivel_tip2_id) {
    $stmt = $conexion->prepare("SELECT texto FROM niveles_tipificacion WHERE id = ?");
    $stmt->execute([$nivel_tip2_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nivel_tip2 = $row['texto'] ?? null;
}
// Guardar monitoreo
$sql = "INSERT INTO monitoreos (
    asesor_id, nombre_asesor, id_llamada, tipo_monitoreo, canal_id,
    criterio1, criterio2, criterio3, criterio4,
    nota_final, fecha_llamada, fecha_monitoreo,
    descripcion, aspectos_mejorar, aspectos_positivos,
    estado, realizado_por, id_usuario,
    nivel_tipificacion, nivel_tipificacion2
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$params = [
    $asesor_id,
    $nombre_asesor,
    $id_llamada,
    $tipo_monitoreo,
    $canal,
    $crit1,
    $crit2,
    $crit3,
    $crit4,
    $nota_final,
    $fecha_llamada,
    $fecha_monitoreo,
    $descripcion,
    $aspectos_mejorar,   // CUIDADO: el orden importa
    $aspectos_pos,
    $estado,
    $realizado_por,
    $id_usuario,
    $nivel_tip1,
    $nivel_tip2
];


$stmt = $conexion->prepare($sql);
if (!$stmt->execute($params)) {
    echo '<h3>Error al guardar monitoreo:</h3>';
    echo '<pre>' . print_r($stmt->errorInfo(), true) . '</pre>';
    exit;
}

$id_monitoreo = $conexion->lastInsertId();

// Guardar ítems de monitoreo
if (!empty($_POST['cumple']) && is_array($_POST['cumple'])) {
    foreach ($_POST['cumple'] as $detalle_item_id => $cumple) {
        $stmt = $conexion->prepare("SELECT valor FROM canal_item_valor WHERE canal_id = ? AND item_id = ?");
        $stmt->execute([$canal, $detalle_item_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $valor = $row['valor'] ?? 0;

        $stmt = $conexion->prepare("SELECT item_id FROM detalle_item WHERE id = ?");
        $stmt->execute([$detalle_item_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $item_id = $row['item_id'] ?? null;

        if ($item_id) {
            $stmt = $conexion->prepare("INSERT INTO monitoreo_items (monitoreo_id, item_id, cumple, valor) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_monitoreo, $item_id, $cumple, $valor]);
        }
    }
}

// Enviar correo
if (!empty($correo_asesor)) {
    $mail = new PHPMailer(true);

    try {
        date_default_timezone_set('America/Bogota');
        $fecha_hora_actual = date('Y-m-d H:i:s');

        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host       = 'sdm.jldesarrolloweb.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@sdm.jldesarrolloweb.com';
        $mail->Password   = 'Movilidad2025*';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('no-reply@sdm.jldesarrolloweb.com', 'Gestión de Calidad SDM');
        $mail->addAddress($correo_asesor, $nombre_asesor);
        $mail->isHTML(true);

        $mail->Subject = "Notificación de nuevo monitoreo de calidad - $fecha_hora_actual";

        $message = "
            <p>Cordial saludo, <strong>{$nombre_asesor}</strong>:</p>
            <p>Te informamos que tu líder de calidad, <strong>{$realizado_por}</strong>, ha realizado un nuevo monitoreo sobre tu gestión.</p>
            <p>Te invitamos a ingresar a <a href='https://sdm.jldesarrolloweb.com' target='_blank'>sdm.jldesarrolloweb.com</a> utilizando tus credenciales para revisar el resultado.</p>
            <p><strong>Recuerda que cuentas con un plazo de 24 horas laborales</strong> para aceptar o refutar el monitoreo. Transcurrido este tiempo, se dará por aceptado de forma automática.</p>
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
        $mail->AltBody = strip_tags(str_replace("</p>", "\n\n", $message));

        $mail->send();

    } catch (Exception $e) {
        error_log("Error al enviar correo al asesor: {$mail->ErrorInfo}");
    }
}

// Confirmación y redirección
echo "
<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <title>Monitoreo creado</title>
  <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Monitoreo creado exitosamente',
    confirmButtonText: 'Aceptar'
  }).then(() => {
    window.location.href = '/usuarios/lider_calidad/monitoreos.php';
  });
</script>
</body>
</html>";
exit;
?>
