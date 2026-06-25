<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

include_once 'bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Traer todos los niveles de tipificación sin filtrar por canal
$stmt = $conexion->prepare("SELECT id, texto FROM nivel_tipificacion1");
$stmt->execute();
$niveles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($niveles);
?>
