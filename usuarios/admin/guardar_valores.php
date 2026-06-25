<?php
// filepath: c:\xampp\htdocs\Gestion_calidad\usuarios\admin\guardar_valores.php

include_once 'bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$canal_id = $_POST['canal_id'];
$valores = $_POST['valores'];
$nombres = $_POST['nombres'];
$criterios = $_POST['criterios'];

foreach ($valores as $item_id => $valor) {
    if ($valor !== "" && $valor !== null) {
        // Convertir a decimal con 2 decimales
        $valor_decimal = round(floatval($valor), 2);

        // Verifica si el ítem ya está asociado en detalle_item
        $stmt_check = $conexion->prepare("SELECT COUNT(*) FROM detalle_item WHERE canal_id = ? AND id = ?");
        $stmt_check->execute([$canal_id, $item_id]);
        $existe = $stmt_check->fetchColumn();

        if (!$existe) {
            // Si no existe, crea el ítem en detalle_item
            $criterio_id = $criterios[$item_id];
            $nombre = $nombres[$item_id];
            $stmt_insert = $conexion->prepare("INSERT INTO detalle_item (canal_id, criterio_id, nombre) VALUES (?, ?, ?)");
            $stmt_insert->execute([$canal_id, $criterio_id, $nombre]);
        }

        // Guarda o actualiza el valor en canal_item_valor
        $stmt_check_valor = $conexion->prepare("SELECT COUNT(*) FROM canal_item_valor WHERE canal_id = ? AND item_id = ?");
        $stmt_check_valor->execute([$canal_id, $item_id]);
        $existe_valor = $stmt_check_valor->fetchColumn();

        if ($existe_valor) {
            $stmt_update = $conexion->prepare("UPDATE canal_item_valor SET valor = ? WHERE canal_id = ? AND item_id = ?");
            $stmt_update->execute([$valor_decimal, $canal_id, $item_id]);
        } else {
            $stmt_insert = $conexion->prepare("INSERT INTO canal_item_valor (canal_id, item_id, valor) VALUES (?, ?, ?)");
            $stmt_insert->execute([$canal_id, $item_id, $valor_decimal]);
        }
    }
}

header("Location:asociar_valores.php?ok=1");
exit;
?>
