<?php
function registrarAuditoria($conexion, $tabla, $operacion, $registro_id, $datos, $usuario_id) {
    $sql = "INSERT INTO auditoria (tabla, operacion, registro_id, datos, usuario_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$tabla, $operacion, $registro_id, json_encode($datos), $usuario_id]);
}