<?php
if (php_sapi_name() !== 'cli') {
    die('Acceso no autorizado.');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

define('HORA_INICIO', 8);
define('HORA_FIN', 18);

function horas_laborales_entre($inicio, $fin) {
    $inicio = new DateTime($inicio);
    $fin = new DateTime($fin);
    if ($fin <= $inicio) return 0;
    $horas = 0;
    $actual = clone $inicio;
    while ($actual < $fin) {
        if ($actual->format('N') < 6) {
            $hora = (int)$actual->format('H');
            if ($hora >= HORA_INICIO && $hora < HORA_FIN) {
                $horas++;
            }
        }
        $actual->modify('+1 hour');
    }
    return $horas;
}

$sql = "SELECT m.id, m.fecha_registro FROM monitoreos m WHERE m.estado = 'pendiente'";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$monitoreos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ahora = date('Y-m-d H:i:s');
foreach ($monitoreos as $m) {
    $horas = horas_laborales_entre($m['fecha_registro'], $ahora);
    if ($horas >= 24) {
        $update = $conexion->prepare("UPDATE monitoreos SET estado = 'aprobado', comentario_agente = ?, fecha_actualizacion = ? WHERE id = ?");
        $update->execute(['Monitoreo aceptado automáticamente', $ahora, $m['id']]);
        echo "[" . date('Y-m-d H:i:s') . "] Actualizado monitoreo ID: {$m['id']}\n";
    }
}
