<?php
include_once "../agente/bd/conexion.php";
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id_monitoreo = $_POST['id_monitoreo'];

// Obtener canal_id del monitoreo
$consulta_canal = "SELECT canal_id FROM monitoreos WHERE id = ?";
$stmt_canal = $conexion->prepare($consulta_canal);
$stmt_canal->execute([$id_monitoreo]);
$canal_id = $stmt_canal->fetchColumn();

// Consulta criterios e ítems del monitoreo usando la tabla monitoreo_items
$consulta = "
    SELECT 
        c.id AS criterio_id,
        c.nombre AS nombre_criterio,
        cp.peso,
        i.id AS item_id,
        i.nombre AS nombre_item,
        mi.cumple,
        mi.valor
        
    FROM monitoreo_items mi
    JOIN items i ON mi.item_id = i.id
    JOIN criterios c ON i.criterio_id = c.id
    LEFT JOIN canal_criterio_peso cp ON cp.criterio_id = c.id AND cp.canal_id = ?
    WHERE mi.monitoreo_id = ?
    ORDER BY c.id, i.id
";
$stmt = $conexion->prepare($consulta);
$stmt->execute([$canal_id, $id_monitoreo]);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupa por criterio
$criterios = [];
foreach ($datos as $row) {
    $cid = $row['criterio_id'];
    if (!isset($criterios[$cid])) {
        $criterios[$cid] = [
            'nombre_criterio' => $row['nombre_criterio'],
            'peso' => $row['peso'],
            'items' => []
        ];
    }
    $criterios[$cid]['items'][] = [
        'nombre_item' => $row['nombre_item'],
        'cumple' => $row['cumple'],
        'valor' => $row['valor']
    ];
}
echo json_encode(array_values($criterios));
?>