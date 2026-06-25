<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$canal_id = isset($_POST['canal_id']) ? $_POST['canal_id'] : 0;
$nivel1_id = isset($_POST['nivel1_id']) ? $_POST['nivel1_id'] : 0;

// Traer los niveles de tipificación 2 según canal y nivel 1
$stmt = $conexion->prepare("
    SELECT id, texto 
    FROM niveles_tipificacion 
    WHERE canal_id = ? AND nivel_padre_id = ?
");
$stmt->execute([$canal_id, $nivel1_id]);
$niveles2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($niveles2);
?>
