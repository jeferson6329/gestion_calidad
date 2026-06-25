<?php
include_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id = (isset($_POST['id'])) ? $_POST['id'] : '';
$nombre = (isset($_POST['nombre'])) ? $_POST['nombre'] : '';
$opcion = (isset($_POST['opcion'])) ? $_POST['opcion'] : '';

switch($opcion){
    case 1: // Crear
        $consulta = "INSERT INTO tmonitoreo (nombre) VALUES (?)";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$nombre]);
        // Obtener el último id insertado
        $id = $conexion->lastInsertId();
        break;
    case 2: // Editar
        $consulta = "UPDATE tmonitoreo SET nombre=? WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$nombre, $id]);
        break;
    case 3: // Eliminar
        $consulta = "DELETE FROM tmonitoreo WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$id]);
        break;
}

// Devuelve id y nombre para actualizar la tabla en JS
echo json_encode(['id' => $id, 'nombre' => $nombre]);
$conexion = null;
?>