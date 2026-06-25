<?php
// filepath: c:\xampp\htdocs\Gestion_calidad\usuarios\admin\obtener_criterios.php

include_once '../admin/bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$canal_id = $_POST['canal_id'] ?? 0;

// Depuración: guarda el canal recibido
file_put_contents('debug.log', "Canal recibido: $canal_id\n", FILE_APPEND);

// Traer criterios SOLO del canal seleccionado
$sql = "SELECT c.id, c.nombre, ccp.peso
        FROM criterios c
        INNER JOIN canal_criterio_peso ccp ON c.id = ccp.criterio_id
        WHERE ccp.canal_id = ?
        ORDER BY c.id";
$stmt = $conexion->prepare($sql);
$stmt->execute([$canal_id]);
$criterios = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents('debug.log', "Criterios encontrados: ".count($criterios)."\n", FILE_APPEND);

$resultado = [];
foreach ($criterios as $criterio) {
    // Traer solo los items de este criterio y su valor para el canal
    $sql_items = "SELECT i.id, i.nombre, civ.valor
                  FROM items i
                  INNER JOIN canal_item_valor civ ON civ.item_id = i.id AND civ.canal_id = ?
                  WHERE i.criterio_id = ?";
    $stmt_items = $conexion->prepare($sql_items);
    $stmt_items->execute([$canal_id, $criterio['id']]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    $criterio['items'] = $items;
    $resultado[] = $criterio;
}

header('Content-Type: application/json');
echo json_encode($resultado);