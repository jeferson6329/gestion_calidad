<?php
include_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id = (isset($_POST['id'])) ? $_POST['id'] : '';
$nombre = (isset($_POST['nombre'])) ? $_POST['nombre'] : '';
$peso = (isset($_POST['peso'])) ? $_POST['peso'] : '';
$opcion = (isset($_POST['opcion'])) ? $_POST['opcion'] : '';

switch($opcion){
    case 1: // Crear
        $consulta = "INSERT INTO criterios (nombre, peso) VALUES (?, ?)";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$nombre, $peso]);
        // Obtener el último id insertado
        $id = $conexion->lastInsertId();
        echo json_encode(['id' => $id, 'nombre' => $nombre, 'peso' => $peso]);
        break;
    case 2: // Editar
        $consulta = "UPDATE criterios SET nombre=?, peso=? WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$nombre, $peso, $id]);
        echo json_encode(['id' => $id, 'nombre' => $nombre, 'peso' => $peso]);
        break;
    case 3: // Eliminar
        $consulta = "DELETE FROM criterios WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $success = $resultado->execute([$id]);
        echo json_encode(['success' => $success]);
        break;
}
$conexion = null;
?>