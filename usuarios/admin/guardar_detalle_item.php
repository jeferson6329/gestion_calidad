<?php
include_once 'bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$canal_id = $_POST['canal_id'];
$detalles = $_POST['detalles'];
$nuevos = isset($_POST['nuevo_item']) ? $_POST['nuevo_item'] : [];

// Actualizar nombres de ítems personalizados existentes
foreach($detalles as $criterio_id => $items){
    foreach($items as $detalle_id => $nombre){
        $stmt_update = $conexion->prepare("UPDATE detalle_item SET nombre = ? WHERE id = ?");
        $stmt_update->execute([$nombre, $detalle_id]);
        // También actualiza el nombre en items si está vinculado
        $stmt_update_item = $conexion->prepare("UPDATE items SET nombre = ? WHERE id = (SELECT item_id FROM detalle_item WHERE id = ?)");
        $stmt_update_item->execute([$nombre, $detalle_id]);
    }
}

// Procesar nuevos ítems personalizados
foreach($nuevos as $criterio_id => $nombre){
    if(trim($nombre) != ''){
        // Puedes obtener el tipo_general según el criterio, por ejemplo:
        // Supongamos que tienes un array $tipos_generales[$criterio_id]
        $tipo_general = isset($tipos_generales[$criterio_id]) ? $tipos_generales[$criterio_id] : '';

        // 1. Crear el ítem en la tabla items y obtener el id
        $stmt_insert_item = $conexion->prepare("INSERT INTO items (criterio_id, nombre, tipo_general) VALUES (?, ?, ?)");
        $stmt_insert_item->execute([$criterio_id, $nombre, $tipo_general]);
        $item_id = $conexion->lastInsertId();

        // 2. Crear el detalle_item con el item_id y tipo_general
        $stmt_insert = $conexion->prepare("INSERT INTO detalle_item (canal_id, criterio_id, item_id, nombre, tipo_general) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->execute([$canal_id, $criterio_id, $item_id, $nombre, $tipo_general]);
    }
}

header("Location: editar_detalle_item.php?canal_id=$canal_id&ok=1");
exit;