<?php
// filepath: c:\xampp\htdocs\Gestion_calidad\usuarios\admin\guardar_asociacion_items.php

include_once '../../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Depuración: muestra los datos recibidos
// Elimina estas líneas cuando funcione
echo '<pre>';
print_r($_POST);
echo '</pre>';
// exit; // Descomenta para depurar

$canal_id = isset($_POST['canal_id']) ? $_POST['canal_id'] : null;
$criterio_id = isset($_POST['criterio_id']) ? $_POST['criterio_id'] : null;
$items = isset($_POST['items']) ? $_POST['items'] : [];

if ($canal_id && $criterio_id && !empty($items)) {
    $consulta_delete = "DELETE FROM asociacion_items WHERE canal_id = ? AND criterio_id = ?";
    $stmt_delete = $conexion->prepare($consulta_delete);
    $stmt_delete->execute([$canal_id, $criterio_id]);

    $consulta_insert = "INSERT INTO asociacion_items (canal_id, criterio_id, item_id) VALUES (?, ?, ?)";
    $stmt_insert = $conexion->prepare($consulta_insert);

    foreach ($items as $item_id) {
        $stmt_insert->execute([$canal_id, $criterio_id, $item_id]);
        if ($stmt_insert->errorCode() !== '00000') {
            echo "Error al insertar: ";
            print_r($stmt_insert->errorInfo());
            exit;
        }
    }

    header("Location: asociar_items.php?ok=1");
    exit;
} else {
    header("Location: asociar_items.php?error=1");
    exit;
}
?>

<form action="guardar_asociacion_items.php" method="POST">
    <select name="canal_id" required>
        <!-- Opciones de canal_id -->
    </select>
    <select name="criterio_id" required>
        <!-- Opciones de criterio_id -->
    </select>
    <input type="checkbox" name="items[]" value="1"> Ítem 1
    <input type="checkbox" name="items[]" value="2"> Ítem 2
    <button type="submit">Guardar</button>
</form>