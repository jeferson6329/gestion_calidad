<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "bd/conexion.php";
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$monitoreo_id = $_GET['id'] ?? 0;

// Obtener el canal del monitoreo
$sql_canal = "SELECT canal_id FROM monitoreos WHERE id = ?";
$stmt_canal = $conexion->prepare($sql_canal);
$stmt_canal->execute([$monitoreo_id]);
$canal_id = $stmt_canal->fetchColumn();

// Traer criterios, ítems y cumplimiento con el valor del criterio según canal
$sql = "SELECT 
            c.id AS criterio_id,
            c.nombre AS criterio,
            ccp.peso AS valor_criterio,
            i.id AS item_id,
            i.nombre AS item,
            mi.cumple
        FROM monitoreo_items mi
        JOIN items i ON mi.item_id = i.id
        JOIN criterios c ON i.criterio_id = c.id
        JOIN canal_criterio_peso ccp ON ccp.criterio_id = c.id AND ccp.canal_id = ?
        WHERE mi.monitoreo_id = ?
        ORDER BY c.id, i.id";
$stmt = $conexion->prepare($sql);
$stmt->execute([$canal_id, $monitoreo_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por criterio
$criterios = [];
foreach ($rows as $row) {
    $cid = $row['criterio_id'];
    if (!isset($criterios[$cid])) {
        $criterios[$cid] = [
            'criterio' => $row['criterio'],
            'valor_criterio' => $row['valor_criterio'],
            'items' => []
        ];
    }
    $criterios[$cid]['items'][] = [
        'item' => $row['item'],
        
        'cumple' => $row['cumple']
    ];
}

echo json_encode(array_values($criterios));