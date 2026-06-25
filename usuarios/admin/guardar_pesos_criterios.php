<?php

session_start();
include_once 'bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

function obtenerIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    else return $_SERVER['REMOTE_ADDR'];
}

if (isset($_POST['canal_id']) && isset($_POST['pesos'])) {
    $canal_id = $_POST['canal_id'];
    $pesos = $_POST['pesos'];

    // Calcular suma (como porcentaje)
    $suma = 0;
    foreach ($pesos as $peso) {
        $suma += floatval($peso);
    }

    if ($suma != 100) {
        header("Location: asociar_pesos_criterios.php?canal_id=$canal_id&error=La suma debe ser exactamente 100%");
        exit;
    }

    try {
        $conexion->beginTransaction();

        // Eliminar los registros anteriores
        $conexion->prepare("DELETE FROM canal_criterio_peso WHERE canal_id = ?")->execute([$canal_id]);

        // Insertar nuevos pesos
        $stmt = $conexion->prepare("INSERT INTO canal_criterio_peso (canal_id, criterio_id, peso) VALUES (?, ?, ?)");
        foreach ($pesos as $criterio_id => $peso) {
            $peso_decimal = floatval($peso) / 100; // Guardar como decimal
            $stmt->execute([$canal_id, $criterio_id, $peso_decimal]);
        }

        $conexion->commit();

        header("Location: asociar_pesos_criterios.php?canal_id=$canal_id&exito=Pesos guardados correctamente");
        exit;

    } catch (Exception $e) {
        $conexion->rollBack();
        header("Location: asociar_pesos_criterios.php?canal_id=$canal_id&error=Error: " . urlencode($e->getMessage()));
        exit;
    }

} else {
    header("Location: asociar_pesos_criterios.php");
    exit;
}
?>
