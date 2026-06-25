<?php
session_start();

include_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id = (isset($_POST['id'])) ? $_POST['id'] : '';
$nombre = (isset($_POST['nombre'])) ? $_POST['nombre'] : '';
$opcion = (isset($_POST['opcion'])) ? $_POST['opcion'] : '';

switch($opcion){
    case 1: // Crear
        $consulta = "INSERT INTO canales (nombre) VALUES (?)";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$nombre]);
        $id = $conexion->lastInsertId();
        echo json_encode(['id' => $id, 'nombre' => $nombre]);
        break;

    case 2: // Editar
        $consulta = "UPDATE canales SET nombre=? WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$nombre, $id]);
        echo json_encode(['id' => $id, 'nombre' => $nombre]);
        break;

    case 3: // Eliminar
        // Aquí solo ejecutamos el DELETE, MySQL hará el borrado en cascada
        $consulta = "DELETE FROM canales WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $success = $resultado->execute([$id]);
        echo json_encode(['success' => $success]);
        break;
}

$conexion = null;
?>
