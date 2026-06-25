<?php
include_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id = isset($_POST['id']) ? $_POST['id'] : '';
$texto = isset($_POST['texto']) ? $_POST['texto'] : '';
$registrado_por = isset($_POST['registrado_por']) ? $_POST['registrado_por'] : '';
$opcion = isset($_POST['opcion']) ? $_POST['opcion'] : '';

switch($opcion){
    case 1: // Insertar
        $consulta = "INSERT INTO nivel_tipificacion1 (texto, registrado_por) VALUES(:texto, :registrado_por)";
        $stmt = $conexion->prepare($consulta);
        $stmt->bindParam(':texto', $texto);
        $stmt->bindParam(':registrado_por', $registrado_por);
        $stmt->execute();
        $id = $conexion->lastInsertId();
        $consulta = "SELECT * FROM nivel_tipificacion1 WHERE id='$id'";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data=$resultado->fetch(PDO::FETCH_ASSOC);
        break;

    case 2: // Editar
        $consulta = "UPDATE nivel_tipificacion1 SET texto=:texto WHERE id=:id";
        $stmt = $conexion->prepare($consulta);
        $stmt->bindParam(':texto', $texto);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $consulta = "SELECT * FROM nivel_tipificacion1 WHERE id='$id'";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data=$resultado->fetch(PDO::FETCH_ASSOC);
        break;

    case 3: // Eliminar
        $consulta = "DELETE FROM nivel_tipificacion1 WHERE id=:id";
        $stmt = $conexion->prepare($consulta);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $data = ['id' => $id];
        break;
}

print json_encode($data, JSON_UNESCAPED_UNICODE);
$conexion=null;
