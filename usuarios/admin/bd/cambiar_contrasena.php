<?php
session_start();
include_once 'conexion.php';

$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id_usuario = $_SESSION['s_id'];

$contraseña_actual = isset($_POST['contraseña_actual']) ? $_POST['contraseña_actual'] : '';
$nueva_contraseña = isset($_POST['nueva_contraseña']) ? $_POST['nueva_contraseña'] : '';
$confirmar_contraseña = isset($_POST['confirmar_contraseña']) ? $_POST['confirmar_contraseña'] : '';

header('Content-Type: application/json');

if ($nueva_contraseña !== $confirmar_contraseña) {
    echo json_encode(['success' => false, 'message' => 'La nueva contraseña y la confirmación no coinciden.']);
    exit;
}

// Verifica la contraseña actual
$consulta = "SELECT contraseña FROM personas WHERE id=?";
$resultado = $conexion->prepare($consulta);
$resultado->execute([$id_usuario]);
$contraseña_bd = $resultado->fetchColumn();

if (!password_verify($contraseña_actual, $contraseña_bd)) {
    echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
    exit;
}

// Actualiza la contraseña
$nueva_contraseña_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
$consulta = "UPDATE personas SET contraseña=? WHERE id=?";
$resultado = $conexion->prepare($consulta);
if ($resultado->execute([$nueva_contraseña_hash, $id_usuario])) {
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña.']);
}
$conexion = null;
?>

