<?php
session_start();
include_once 'conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Función para registrar auditoría
function registrarAuditoria($conexion, $tabla, $registro_id, $accion, $usuario_id, $usuario_nombre, $detalles = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
    $dispositivo = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
    
    $sql = "INSERT INTO auditoria (tabla, registro_id, accion, usuario_id, usuario_nombre, ip, dispositivo, detalles) 
            VALUES (:tabla, :registro_id, :accion, :usuario_id, :usuario_nombre, :ip, :dispositivo, :detalles)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':tabla' => $tabla,
        ':registro_id' => $registro_id,
        ':accion' => $accion,
        ':usuario_id' => $usuario_id,
        ':usuario_nombre' => $usuario_nombre,
        ':ip' => $ip,
        ':dispositivo' => $dispositivo,
        ':detalles' => $detalles
    ]);
}

// Registrar auditoría de logout si hay usuario logueado
if (isset($_SESSION["s_id"]) && isset($_SESSION["s_nombre"])) {
    registrarAuditoria(
        $conexion,
        'personas',
        $_SESSION["s_id"],
        'LOGOUT',
        $_SESSION["s_id"],
        $_SESSION["s_nombre"],
        'Usuario cerró sesión'
    );
}

// Destruir sesión
unset($_SESSION["s_usuario"]);
session_destroy();

// Redirigir
header("Location:../index.php");
$conexion = null;
?>
