<?php require_once "vistas/parte_superior.php"?>
<?php
include_once '../agente/bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id_agente = $_SESSION['s_id'];

// Totales generales (desde inicio de contrato)
$sql_total = "SELECT COUNT(*) AS total, AVG(nota_final) AS promedio FROM monitoreos WHERE asesor_id = ?";
$stmt_total = $conexion->prepare($sql_total);
$stmt_total->execute([$id_agente]);
$row_total = $stmt_total->fetch(PDO::FETCH_ASSOC);
$total_monitoreos = $row_total['total'];
$promedio_general = $row_total['promedio'] ? round($row_total['promedio'], 2) : 0;

$estados = ['aprobado', 'refutado', 'reevaluado', 'pendiente'];
$totales_estado = [];
$promedios_estado = [];
foreach ($estados as $estado) {
    $sql = "SELECT COUNT(*) AS total, AVG(nota_final) AS promedio FROM monitoreos WHERE asesor_id = ? AND estado = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_agente, $estado]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totales_estado[$estado] = $row['total'];
    $promedios_estado[$estado] = $row['promedio'] ? round($row['promedio'], 2) : 0;
}

// Totales del mes en curso
$mes_actual = date('m');
$anio_actual = date('Y');
$sql_mes = "SELECT COUNT(*) AS total_mes, AVG(nota_final) AS promedio_mes FROM monitoreos WHERE asesor_id = ? AND MONTH(fecha_monitoreo) = ? AND YEAR(fecha_monitoreo) = ?";
$stmt_mes = $conexion->prepare($sql_mes);
$stmt_mes->execute([$id_agente, $mes_actual, $anio_actual]);
$row_mes = $stmt_mes->fetch(PDO::FETCH_ASSOC);
$total_mes = $row_mes['total_mes'];
$promedio_mes = $row_mes['promedio_mes'] ? round($row_mes['promedio_mes'], 2) : 0;

$totales_estado_mes = [];
$promedios_estado_mes = [];
foreach ($estados as $estado) {
    $sql = "SELECT COUNT(*) AS total, AVG(nota_final) AS promedio FROM monitoreos WHERE asesor_id = ? AND estado = ? AND MONTH(fecha_monitoreo) = ? AND YEAR(fecha_monitoreo) = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_agente, $estado, $mes_actual, $anio_actual]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totales_estado_mes[$estado] = $row['total'];
    $promedios_estado_mes[$estado] = $row['promedio'] ? round($row['promedio'], 2) : 0;
}
?>

<div class="container mt-4">
    <div class="alert alert-info text-center" role="alert">
        <h5>Desde el inicio de contrato hasta la fecha</h5>
        <strong>Total de monitoreos realizados:</strong> <span class="badge badge-dark"><?php echo $total_monitoreos; ?></span>
        <br>
        <strong>Aprobados:</strong> <span class="badge badge-success"><?php echo $totales_estado['aprobado']; ?></span>
        &nbsp;|&nbsp;
        <strong>Refutados:</strong> <span class="badge badge-danger"><?php echo $totales_estado['refutado']; ?></span>
        &nbsp;|&nbsp;
        <strong>Re-evaluados:</strong> <span class="badge badge-info"><?php echo $totales_estado['reevaluado']; ?></span>
        &nbsp;|&nbsp;
        <strong>Pendientes:</strong> <span class="badge badge-secondary"><?php echo $totales_estado['pendiente']; ?></span>
        <br>
        <strong>Promedio general:</strong> <span class="badge badge-warning"><?php echo $promedio_general; ?></span>
    </div>
</div>

<div class="container mt-4">
    <div class="alert alert-primary text-center" role="alert">
        <h5>Del mes en curso</h5>
        <strong>Total de monitoreos realizados:</strong> <span class="badge badge-dark"><?php echo $total_mes; ?></span>
        <br>
        <strong>Aprobados:</strong> <span class="badge badge-success"><?php echo $totales_estado_mes['aprobado']; ?></span>
        &nbsp;|&nbsp;
        <strong>Refutados:</strong> <span class="badge badge-danger"><?php echo $totales_estado_mes['refutado']; ?></span>
        &nbsp;|&nbsp;
        <strong>Re-evaluados:</strong> <span class="badge badge-info"><?php echo $totales_estado_mes['reevaluado']; ?></span>
        &nbsp;|&nbsp;
        <strong>Pendientes:</strong> <span class="badge badge-secondary"><?php echo $totales_estado_mes['pendiente']; ?></span>
        <br>
        <strong>Promedio este mes:</strong> <span class="badge badge-warning"><?php echo $promedio_mes; ?></span>
    </div>
</div>


<div class="row alert alert-primary text-center justify-content-center" role="alert">
    <div class="col-auto">
        <a href="monitoreosagente.php" class="btn btn-dark">Ver todos</a>
    </div>
    <div class="col-auto">
        <a href="monitoreosagente.php?estado=pendiente" class="btn btn-secondary">Pendientes</a>
    </div>
    <div class="col-auto">
        <a href="monitoreosagente.php?estado=aprobado" class="btn btn-success">Aprobados</a>
    </div>
    <div class="col-auto">
        <a href="monitoreosagente.php?estado=refutado" class="btn btn-danger">Refutados</a>
    </div>
    <div class="col-auto">
        <a href="monitoreosagente.php?estado=reevaluado" class="btn btn-info">Re-evaluados</a>
    </div>
</div>


<!-- Promedio mensual histórico del agente -->
<div class="container mb-4">
    <h5 class="text-center">Promedio por mes</h5>
    <table class="table table-bordered table-sm text-center" style="max-width:400px;margin:auto;">
        <thead>
            <tr>
                <th>Mes</th>
                <th>Promedio (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta para obtener promedio por mes y año
            $sql_hist = "SELECT YEAR(fecha_monitoreo) AS anio, MONTH(fecha_monitoreo) AS mes, AVG(nota_final) AS promedio
                         FROM monitoreos
                         WHERE asesor_id = ?
                         GROUP BY anio, mes
                         ORDER BY anio DESC, mes DESC";
            $stmt_hist = $conexion->prepare($sql_hist);
            $stmt_hist->execute([$id_agente]);
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            while ($row = $stmt_hist->fetch(PDO::FETCH_ASSOC)) {
                $color = ($row['promedio'] < 94.9) ? 'danger' : 'warning';
                echo "<tr>
                        <td>{$meses[(int)$row['mes']]} {$row['anio']}</td>
                        <td><span class='badge badge-$color'>".round($row['promedio'],2)."%</span></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php require_once "vistas/parte_inferior.php"?>