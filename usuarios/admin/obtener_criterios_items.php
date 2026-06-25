<?php
// filepath: c:\xampp\htdocs\Gestion_calidad\usuarios\admin\obtener_criterios_items.php

include_once '../../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$canal_id = isset($_POST['canal_id']) ? $_POST['canal_id'] : null;
$resultado = [];

if ($canal_id) {
    // Traer criterios asociados al canal
    $consulta_criterios = "
        SELECT DISTINCT c.id, c.nombre
        FROM asociacion_items ai
        JOIN criterios c ON ai.criterio_id = c.id
        WHERE ai.canal_id = ?
    ";
    $stmt_criterios = $conexion->prepare($consulta_criterios);
    $stmt_criterios->execute([$canal_id]);
    $criterios = $stmt_criterios->fetchAll(PDO::FETCH_ASSOC);

    foreach($criterios as $criterio){
        // Traer ítems asociados a ese canal y criterio
        $consulta_items = "
            SELECT i.id, i.nombre
            FROM asociacion_items ai
            JOIN items i ON ai.item_id = i.id
            WHERE ai.canal_id = ? AND ai.criterio_id = ?
        ";
        $stmt_items = $conexion->prepare($consulta_items);
        $stmt_items->execute([$canal_id, $criterio['id']]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        $resultado[] = [
            'id' => $criterio['id'],
            'nombre' => $criterio['nombre'],
            'items' => $items
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($resultado);