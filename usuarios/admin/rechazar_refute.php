<?php

include_once "bd/conexion.php";
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id_monitoreo = $_POST['id_monitoreo'] ?? null;
$razones = $_POST['razones'] ?? '';
$comentario_supervisor = $_POST['comentario_supervisor'] ?? '';

if ($id_monitoreo && $razones) {
    $sql = "UPDATE monitoreos SET estado = 'pendiente', razones_rechazo = ?, comentario_supervisor = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    echo json_encode(['success' => $stmt->execute([$razones, $comentario_supervisor, $id_monitoreo])]);
} else {
    echo json_encode(['success' => false]);
}