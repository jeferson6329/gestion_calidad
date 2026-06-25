<?php require_once "vistas/parte_superior.php"; ?>
<div class="container">
    <h1>Contenido principal</h1>
</div>
<?php
include_once 'bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Ahora sí puedes acceder a $_SESSION
$supervisor_id = $_SESSION['s_id'];

// Total de agentes
$consulta = "SELECT COUNT(*) AS total_agentes FROM personas WHERE rol = 'agente'";
$resultado = $conexion->prepare($consulta);
$resultado->execute();
$fila = $resultado->fetch(PDO::FETCH_ASSOC);
$total_agentes = $fila['total_agentes'];

// Total de usuarios
$consulta_total = "SELECT COUNT(*) AS total_usuarios FROM personas";
$resultado_total = $conexion->prepare($consulta_total);
$resultado_total->execute();
$fila_total = $resultado_total->fetch(PDO::FETCH_ASSOC);
$total_usuarios = $fila_total['total_usuarios'];

// Total de monitoreos
$consulta_total = "SELECT COUNT(*) AS total_monitoreos FROM monitoreos";
$resultado_total = $conexion->prepare($consulta_total);
$resultado_total->execute();
$fila_total = $resultado_total->fetch(PDO::FETCH_ASSOC);
$total_monitoreos = $fila_total['total_monitoreos'];

// Totales por estado
$estados = ['pendiente', 'aprobado', 'refutado', 'reevaluado'];
$totales_estado = [];
foreach($estados as $estado){
    $consulta = "SELECT COUNT(*) AS total FROM monitoreos WHERE estado = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->execute([$estado]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    $totales_estado[$estado] = $fila['total'];
}

// Totales por rol
$roles = ['administrador', 'lider de calidad', 'agente', 'supervisor'];
$totales_rol = [];
foreach($roles as $rol){
    $consulta = "SELECT COUNT(*) AS total FROM personas WHERE rol = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->execute([$rol]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    $totales_rol[$rol] = $fila['total'];
}

// Obtener el mes y año actual
$mes_actual = date('m');
$anio_actual = date('Y');

// Total de monitoreos del mes en curso
$consulta_mes = "SELECT COUNT(*) AS total_mes FROM monitoreos WHERE MONTH(fecha_monitoreo) = ? AND YEAR(fecha_monitoreo) = ?";
$stmt_mes = $conexion->prepare($consulta_mes);
$stmt_mes->execute([$mes_actual, $anio_actual]);
$fila_mes = $stmt_mes->fetch(PDO::FETCH_ASSOC);
$total_mes = $fila_mes['total_mes'];

// Totales por estado del mes en curso
$totales_estado_mes = [];
foreach($estados as $estado){
    $consulta = "SELECT COUNT(*) AS total FROM monitoreos WHERE estado = ? AND MONTH(fecha_monitoreo) = ? AND YEAR(fecha_monitoreo) = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->execute([$estado, $mes_actual, $anio_actual]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    $totales_estado_mes[$estado] = $fila['total'];
}

// Promedio general del mes en curso
$consulta_promedio_general = "SELECT AVG(nota_final) AS promedio_general FROM monitoreos WHERE MONTH(fecha_monitoreo) = ? AND YEAR(fecha_monitoreo) = ?";
$stmt_promedio_general = $conexion->prepare($consulta_promedio_general);
$stmt_promedio_general->execute([$mes_actual, $anio_actual]);
$fila_promedio_general = $stmt_promedio_general->fetch(PDO::FETCH_ASSOC);
$promedio_general_mes = round($fila_promedio_general['promedio_general'], 2);

// Promedio por agente del mes en curso
$consulta_promedio_agentes = "
    SELECT p.nombre, AVG(m.nota_final) AS promedio_agente
    FROM monitoreos m
    JOIN personas p ON m.asesor_id = p.id
    WHERE MONTH(m.fecha_monitoreo) = ? AND YEAR(m.fecha_monitoreo) = ?
    GROUP BY m.asesor_id
";
$stmt_promedio_agentes = $conexion->prepare($consulta_promedio_agentes);
$stmt_promedio_agentes->execute([$mes_actual, $anio_actual]);
$promedios_agentes = $stmt_promedio_agentes->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="alert alert-info text-center" role="alert">
        Total de usuarios: <span class="badge badge-success"><?php echo $total_usuarios; ?></span>
        &nbsp;|&nbsp;
        Total de usuarios con rol <strong>agente</strong>: <span class="badge badge-primary"><?php echo $total_agentes; ?></span>
    </div>
</div>

<div class="container mt-4">
    <div class="row justify-content-center">
        
        <div class="col-auto">
            <a href="usuarios.php?rol=lider de calidad" class="btn btn-info">
                Líderes de calidad <span class="badge badge-light"><?php echo $totales_rol['lider de calidad']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="usuarios.php?rol=agente" class="btn btn-success">
                Agentes <span class="badge badge-light"><?php echo $totales_rol['agente']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="usuarios.php?rol=supervisor" class="btn btn-warning">
                Supervisores <span class="badge badge-light"><?php echo $totales_rol['supervisor']; ?></span>
            </a>
        </div>
    </div>
</div>
<div class="container mt-4">
<div class="alert alert-info text-center" role="alert">
    <strong>Total de monitoreos general:</strong>
    <span class="badge badge-warning"><?php echo $total_monitoreos; ?></span>
    <br>
    <strong>Total de monitoreos este mes:</strong>
    <span class="badge badge-primary"><?php echo $total_mes; ?></span>
    <br>
    <strong>Pendientes:</strong>
    <span class="badge badge-secondary"><?php echo $totales_estado['pendiente']; ?></span>
    &nbsp;|&nbsp;
    <strong>Aprobados:</strong>
    <span class="badge badge-success"><?php echo $totales_estado['aprobado']; ?></span>
    &nbsp;|&nbsp;
    <strong>Refutados:</strong>
    <span class="badge badge-danger"><?php echo $totales_estado['refutado']; ?></span>
    &nbsp;|&nbsp;
    <strong>Re-evaluados:</strong>
    <span class="badge badge-info"><?php echo $totales_estado['reevaluado']; ?></span>
    <br>
    <strong>Promedio general:</strong>
    <span class="badge badge-dark">
        <?php
        // Promedio general de todos los monitoreos
        $consulta_promedio_general_total = "SELECT AVG(nota_final) AS promedio_general_total FROM monitoreos";
        $stmt_promedio_general_total = $conexion->prepare($consulta_promedio_general_total);
        $stmt_promedio_general_total->execute();
        $fila_promedio_general_total = $stmt_promedio_general_total->fetch(PDO::FETCH_ASSOC);
        echo round($fila_promedio_general_total['promedio_general_total'], 2);
        ?>
    </span>
</div>
    <div class="row justify-content-center">
        <div class="col-auto">
            <a href="monitoreos.php?estado=pendiente" class="btn btn-secondary">
                Pendientes <span class="badge badge-light"><?php echo $totales_estado['pendiente']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="monitoreos.php?estado=aprobado" class="btn btn-success">
                Aprobados <span class="badge badge-light"><?php echo $totales_estado['aprobado']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="monitoreos.php?estado=refutado" class="btn btn-danger">
                Refutados <span class="badge badge-light"><?php echo $totales_estado['refutado']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="monitoreos.php?estado=reevaluado" class="btn btn-info">
                Re-evaluados <span class="badge badge-light"><?php echo $totales_estado['reevaluado']; ?></span>
            </a>
        </div>
    </div>
</div>

<!-- Sección de monitoreos y promedios del mes en curso -->


<!-- Tabla de promedios por agente -->
<div class="container mt-4">
    <div class="card">
        <div class="card-header text-center">
            <strong>Promedio por agente</strong>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Agente</th>
                        <th>Monitoreos este mes</th>
                        <th>Monitoreos total</th>
                        <th>Promedio este mes</th>
                        <th>Promedio general</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Monitoreos y promedio este mes
                    $consulta_mes = "
                        SELECT p.id, p.nombre, COUNT(m.id) AS monitoreos_mes, AVG(m.nota_final) AS promedio_mes
                        FROM monitoreos m
                        JOIN personas p ON m.asesor_id = p.id
                        WHERE MONTH(m.fecha_monitoreo) = ? AND YEAR(m.fecha_monitoreo) = ?
                        GROUP BY m.asesor_id
                    ";
                    $stmt_mes = $conexion->prepare($consulta_mes);
                    $stmt_mes->execute([$mes_actual, $anio_actual]);
                    $agentes_mes = $stmt_mes->fetchAll(PDO::FETCH_ASSOC);

                    // Monitoreos y promedio general
                    $consulta_total = "
                        SELECT p.id, p.nombre, COUNT(m.id) AS monitoreos_total, AVG(m.nota_final) AS promedio_general
                        FROM monitoreos m
                        JOIN personas p ON m.asesor_id = p.id
                        GROUP BY m.asesor_id
                    ";
                    $stmt_total = $conexion->prepare($consulta_total);
                    $stmt_total->execute();
                    $agentes_total = $stmt_total->fetchAll(PDO::FETCH_ASSOC);

                    // Indexar por id para acceso rápido
                    $agentes_total_idx = [];
                    foreach($agentes_total as $agente){
                        $agentes_total_idx[$agente['id']] = $agente;
                    }

                    // Mostrar la tabla
                    foreach($agentes_mes as $agente_mes):
                        $id = $agente_mes['id'];
                        $nombre = $agente_mes['nombre'];
                        $monitoreos_mes = $agente_mes['monitoreos_mes'];
                        $promedio_mes = round($agente_mes['promedio_mes'], 2);

                        $monitoreos_total = isset($agentes_total_idx[$id]) ? $agentes_total_idx[$id]['monitoreos_total'] : 0;
                        $promedio_general = isset($agentes_total_idx[$id]) ? round($agentes_total_idx[$id]['promedio_general'], 2) : '-';
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nombre); ?></td>
                            <td><?php echo $monitoreos_mes; ?></td>
                            <td><?php echo $monitoreos_total; ?></td>
                            <td><?php echo $promedio_mes; ?></td>
                            <td><?php echo $promedio_general; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// Meses en español
$meses_es = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Calcular los últimos 6 meses
$meses = [];
$labels = [];
for ($i = 5; $i >= 0; $i--) {
    $fecha = strtotime("-$i month");
    $mes_num = (int)date('m', $fecha);
    $anio_num = (int)date('Y', $fecha);
    $meses[] = ['mes' => $mes_num, 'anio' => $anio_num];
    $labels[] = $meses_es[$mes_num] . " $anio_num";
}

// Consulta para obtener promedios por agente y mes (solo últimos 6 meses y solo agentes del supervisor)
$fechas_where = [];
foreach ($meses as $m) {
    $fechas_where[] = "(MONTH(m.fecha_monitoreo) = {$m['mes']} AND YEAR(m.fecha_monitoreo) = {$m['anio']})";
}
$where = implode(' OR ', $fechas_where);

$sql = "SELECT p.nombre, p.documento, YEAR(m.fecha_monitoreo) AS anio, MONTH(m.fecha_monitoreo) AS mes, AVG(m.nota_final) AS promedio
        FROM monitoreos m
        JOIN personas p ON m.asesor_id = p.id
        WHERE ($where) AND p.supervisor_id = ?
        GROUP BY p.id, anio, mes
        ORDER BY p.nombre, anio DESC, mes DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute([$supervisor_id]);

// Organizar los datos por agente y mes
$datos = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $nombre = $row['nombre'];
    $cedula = $row['documento'];
    $anio = $row['anio'];
    $mes = (int)$row['mes'];
    $promedio = round($row['promedio'], 2);

    if (!isset($datos[$nombre.'_'.$cedula])) {
        $datos[$nombre.'_'.$cedula] = [
            'nombre' => $nombre,
            'cedula' => $cedula,
            'meses' => []
        ];
    }
    $datos[$nombre.'_'.$cedula]['meses']["$mes-$anio"] = $promedio;
}
?>

<div class="container mb-4">
    <div class="card">
        <div class="card-header text-center">
            <strong>Reporte mensual por agente (últimos 6 meses)</strong>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm text-center">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <?php
                        foreach($labels as $label) {
                            echo "<th>$label</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($datos as $agente) {
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($agente['nombre'])."</td>";
                        echo "<td>".htmlspecialchars($agente['cedula'])."</td>";
                        foreach ($meses as $m) {
                            $key = "{$m['mes']}-{$m['anio']}";
                            if (isset($agente['meses'][$key])) {
                                $prom = $agente['meses'][$key];
                                $color = ($prom < 94.9) ? 'danger' : 'warning';
                                echo "<td><span class='badge badge-$color'>$prom</span></td>";
                            } else {
                                echo "<td>-</td>";
                            }
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php require_once "vistas/parte_inferior.php"?>