
<?php
include_once 'conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$documento = isset($_POST['documento']) ? $_POST['documento'] : '';
$correo = isset($_POST['correo']) ? $_POST['correo'] : '';
$id = isset($_POST['id']) ? $_POST['id'] : '';

$response = ['documento' => false, 'correo' => false];

// Validar documento
if ($documento != '') {
    if ($id) {
        $consulta = "SELECT COUNT(*) FROM personas WHERE documento=? AND id<>?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$documento, $id]);
    } else {
        $consulta = "SELECT COUNT(*) FROM personas WHERE documento=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$documento]);
    }
    $response['documento'] = $resultado->fetchColumn() > 0;
}

// Validar correo
if ($correo != '') {
    if ($id) {
        $consulta = "SELECT COUNT(*) FROM personas WHERE correo=? AND id<>?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$correo, $id]);
    } else {
        $consulta = "SELECT COUNT(*) FROM personas WHERE correo=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$correo]);
    }
    $response['correo'] = $resultado->fetchColumn() > 0;
}

echo json_encode($response);