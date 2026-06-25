<?php
include_once '../agente/bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id = $_POST['id'];
$comentario = $_POST['comentario'];

$consulta = "UPDATE monitoreos SET estado='aprobado', comentario_agente=? WHERE id=?";
$resultado = $conexion->prepare($consulta);
$resultado->execute([$comentario, $id]);

if($resultado->rowCount() > 0){
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No se actualizó ningún registro. ID recibido: '.$id,
        'sql_error' => $resultado->errorInfo()
    ]);
}
$conexion = null;
?>