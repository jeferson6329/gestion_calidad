<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include_once '/xampp/htdocs/Gestion_calidad/usuarios/agente/bd/conexion.php';

$objeto = new Conexion();
$conexion = $objeto->Conectar();

$response = ['success' => false, 'msg' => 'Error desconocido'];

if (!isset($_SESSION['s_id'])) {
    $response['msg'] = 'Sesión no válida';
    echo json_encode($response);
    exit;
}

$usuario_id = $_SESSION['s_id'];
$actual = $_POST['contraseña_actual'] ?? '';
$nueva = $_POST['nueva_contraseña'] ?? '';
$confirmar = $_POST['confirmar_contraseña'] ?? '';

if ($nueva !== $confirmar) {
    $response['msg'] = 'Las contraseñas no coinciden';
    echo json_encode($response);
    exit;
}

// Verifica contraseña actual
$sql = "SELECT contraseña FROM personas WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$usuario_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || md5($actual) !== $row['contraseña']) {
    $response['msg'] = 'Contraseña actual incorrecta';
    echo json_encode($response);
    exit;
}

// Actualiza la contraseña
$nueva_hash = md5($nueva);
$sql = "UPDATE personas SET contraseña = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);
if ($stmt->execute([$nueva_hash, $usuario_id])) {
    $response['success'] = true;
    $response['msg'] = 'Contraseña cambiada correctamente';
} else {
    $response['msg'] = 'Error al actualizar la contraseña';
}
echo json_encode($response);
$conexion = null;
?>

