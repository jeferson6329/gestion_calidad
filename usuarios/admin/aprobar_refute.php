<?php

include_once "bd/conexion.php";
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id_monitoreo = $_POST['id_monitoreo'] ?? null;
$comentario_supervisor = $_POST['comentario_supervisor'] ?? '';

if ($id_monitoreo) {
    $sql = "UPDATE monitoreos SET estado = 'refutado', comentario_supervisor = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    echo json_encode(['success' => $stmt->execute([$comentario_supervisor, $id_monitoreo])]);
} else {
    echo json_encode(['success' => false]);
}