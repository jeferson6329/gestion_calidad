<?php
require_once "../agente/vistas/parte_superior.php";
include_once "../agente/bd/conexion.php";
$objeto = new Conexion();
$conexion = $objeto->Conectar();




if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$asesor_id = $_SESSION['s_id'];

$tipos_monitoreo = $conexion->query("SELECT id, nombre FROM tmonitoreo")->fetchAll(PDO::FETCH_ASSOC);
$canales = $conexion->query("SELECT id, nombre FROM canales")->fetchAll(PDO::FETCH_ASSOC);

$where = "WHERE m.asesor_id = ?";
$params = [$asesor_id];
$estados = ['pendiente', 'aprobado', 'refutado', 'reevaluado', 'en revision'];
$totales_estado = [];
foreach($estados as $estado){
    $consulta = "SELECT COUNT(*) AS total FROM monitoreos WHERE estado = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->execute([$estado]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    $totales_estado[$estado] = $fila['total'];
}
if (!empty($_GET['tipo_monitoreo'])) {
    $where .= " AND m.tipo_monitoreo = ?";
    $params[] = $_GET['tipo_monitoreo'];
}
if (!empty($_GET['canal'])) {
    $where .= " AND m.canal_id = ?";
    $params[] = $_GET['canal'];
}
if (!empty($_GET['fecha_desde'])) {
    $where .= " AND m.fecha_monitoreo >= ?";
    $params[] = $_GET['fecha_desde'];
}
if (!empty($_GET['fecha_hasta'])) {
    $where .= " AND m.fecha_monitoreo <= ?";
    $params[] = $_GET['fecha_hasta'];
}
if (!empty($_GET['estado'])) {
    $where .= " AND m.estado = ?";
    $params[] = $_GET['estado'];
}

$consulta = "SELECT m.id, m.id_llamada, m.nombre_asesor, tm.nombre AS tipo_monitoreo, c.nombre AS canal, 
    m.criterio1, m.criterio2, m.criterio3, m.criterio4, m.nota_final, m.fecha_llamada, m.fecha_monitoreo, 
    m.descripcion, m.aspectos_positivos, m.aspectos_mejorar, m.comentario_refute, m.estado, m.realizado_por, m.comentario_agente, m.fecha_registro
    FROM monitoreos m
    LEFT JOIN tmonitoreo tm ON m.tipo_monitoreo = tm.id
    LEFT JOIN canales c ON m.canal_id = c.id
    $where
    ORDER BY m.id DESC";
$resultado = $conexion->prepare($consulta);
$resultado->execute($params);
$data = $resultado->fetchAll(PDO::FETCH_ASSOC);

// Festivos para JS
$festivos = $conexion->query("SELECT fecha FROM dias_festivos")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Variable festivos para JS -->
<script>
    var festivos = <?php echo json_encode($festivos); ?>;
    var tiposMonitoreo = <?php echo json_encode($tipos_monitoreo); ?>;
    var canales = <?php echo json_encode($canales); ?>;
</script>

<style>
#detalle_criterios table {
    width: 100%;
    table-layout: fixed; /* Hace que el ancho se reparta fijo */
}

#detalle_criterios th:nth-child(1),
#detalle_criterios td:nth-child(1) {
    width: calc(100% - 80px); /* Toda la anchura menos la columna "Cumple" */
    word-wrap: break-word;
    white-space: normal;
}

#detalle_criterios th:nth-child(2),
#detalle_criterios td:nth-child(2) {
    width: 80px;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}
#detalle_criterios .badge {
    display: inline-block;
    min-width: 50px;
    text-align: center;
}

</style>


<div class="container">
    <h2 class="mb-3">Monitoreos del Agente</h2>
    <div class="row mb-3">
        <div class="col-auto">
            <a href="monitoreosagente.php" class="btn btn-secondary">
                Ver todos <span class="badge badge-light"><?php echo $totales_estado['pendiente']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="monitoreosagente.php?estado=pendiente" class="btn btn-secondary">
                Pendientes <span class="badge badge-light"><?php echo $totales_estado['pendiente']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="monitoreosagente.php?estado=aprobado" class="btn btn-success">
                Aprobados <span class="badge badge-light"><?php echo $totales_estado['aprobado']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="monitoreosagente.php?estado=refutado" class="btn btn-danger">
                Refutados <span class="badge badge-light"><?php echo $totales_estado['refutado']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="monitoreosagente.php?estado=reevaluado" class="btn btn-info">
                Re-evaluados <span class="badge badge-light"><?php echo $totales_estado['reevaluado']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="monitoreosagente.php?estado=en revision" class="btn btn-warning">
                En revisión <span class="badge badge-light"><?php echo $totales_estado['en revision']; ?></span>
            </a>
        </div>
    </div>
    <!-- Filtros avanzados -->
    <div class="row mb-2">
        <div class="col-md-3">
            <select id="filtroCampo" class="form-control">
                <option value="">Filtrar por...</option>
                <option value="tipo_monitoreo">Tipo de monitoreo</option>
                <option value="canal">Canal</option>
                <option value="fecha">Fecha monitoreo</option>
            </select>
        </div>
        <div class="col-md-4" id="contenedorFiltroOpciones"></div>
    </div>

    <div class="table-responsive">
        <table id="tablamonitoreos" class="table table-striped table-bordered">
            <thead class="text-center">
                <tr>
                    <th style="display:none;">ID</th>
                    <th style="display:none;">Nombre Asesor</th>
                    <th>ID Llamada</th>
                    <th>Tipo Monitoreo</th>
                    <th>Canal</th>
                    <th>ECN</th>
                    <th>ECUF</th>
                    <th>ECC</th>
                    <th>ENC</th>
                    <th>Nota Final</th>
                    <th>Fecha Llamada</th>
                    <th>Fecha Monitoreo</th>
                    <th>Estado</th>
                    <th>Realizado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $dat) { ?>
                <tr 
                    data-id="<?= $dat['id'] ?>"
                    data-id_llamada="<?= htmlspecialchars($dat['id_llamada']) ?>"
                    data-nombre_asesor="<?= htmlspecialchars($dat['nombre_asesor']) ?>"
                    data-tipo_monitoreo="<?= htmlspecialchars($dat['tipo_monitoreo']) ?>"
                    data-canal="<?= htmlspecialchars($dat['canal']) ?>"
                    data-criterio1="<?= htmlspecialchars($dat['criterio1']) ?>"
                    data-criterio2="<?= htmlspecialchars($dat['criterio2']) ?>"
                    data-criterio3="<?= htmlspecialchars($dat['criterio3']) ?>"
                    data-criterio4="<?= htmlspecialchars($dat['criterio4']) ?>"
                    data-nota_final="<?= htmlspecialchars($dat['nota_final']) ?>"
                    data-fecha_llamada="<?= htmlspecialchars($dat['fecha_llamada']) ?>"
                    data-fecha_monitoreo="<?= htmlspecialchars($dat['fecha_monitoreo']) ?>"
                    data-estado="<?= htmlspecialchars($dat['estado']) ?>"
                    data-realizado_por="<?= htmlspecialchars($dat['realizado_por']) ?>"
                    data-descripcion="<?= htmlspecialchars($dat['descripcion'] ?? '') ?>"
                    data-positivos="<?= htmlspecialchars($dat['aspectos_positivos'] ?? '') ?>"
                    data-mejorar="<?= htmlspecialchars($dat['aspectos_mejorar'] ?? '') ?>"
                    data-comentario_refute="<?= htmlspecialchars($dat['comentario_refute'] ?? '') ?>"
                    data-comentario_agente="<?= htmlspecialchars($dat['comentario_agente'] ?? '') ?>"
                    data-fecha_registro="<?= htmlspecialchars($dat['fecha_registro']) ?>"
                >
                    <td style="display:none;"><?= $dat['id'] ?></td>
                    <td style="display:none;"><?= htmlspecialchars($dat['nombre_asesor']) ?></td>
                    <td><?= htmlspecialchars($dat['id_llamada']) ?></td>
                    <td><?= htmlspecialchars($dat['tipo_monitoreo']) ?></td>
                    <td><?= htmlspecialchars($dat['canal']) ?></td>
                    <td><?= htmlspecialchars($dat['criterio1']) ?></td>
                    <td><?= htmlspecialchars($dat['criterio2']) ?></td>
                    <td><?= htmlspecialchars($dat['criterio3']) ?></td>
                    <td><?= htmlspecialchars($dat['criterio4']) ?></td>
                    <td><?= htmlspecialchars($dat['nota_final']) ?></td>
                    <td><?= htmlspecialchars($dat['fecha_llamada']) ?></td>
                    <td><?= htmlspecialchars($dat['fecha_monitoreo']) ?></td>
                    <td><?= htmlspecialchars($dat['estado']) ?></td>
                    <td><?= htmlspecialchars($dat['realizado_por']) ?></td>
                    <td>
                        <button class="btn btn-info btn-sm btnVerMas">Ver más</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-labelledby="modalDetalleLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle del Monitoreo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info" id="mensajeTiempoRestante" style="display:none;">
            Le queda <span id="contadorTiempoRestante"></span> para aceptar o refutar el monitoreo.
        </div>
        <table class="table table-bordered">
            <tr><th>ID Llamada</th><td id="detalle_id_llamada"></td></tr>
            <tr><th>Nombre Asesor</th><td id="detalle_nombre_asesor"></td></tr>
            <tr><th>Tipo Monitoreo</th><td id="detalle_tipo_monitoreo"></td></tr>
            <tr><th>Canal</th><td id="detalle_canal"></td></tr>
            <tr><th>ECN</th><td id="detalle_criterio1"></td></tr>
            <tr><th>ECUF</th><td id="detalle_criterio2"></td></tr>
            <tr><th>ECC</th><td id="detalle_criterio3"></td></tr>
            <tr><th>ENC</th><td id="detalle_criterio4"></td></tr>
            <tr><th>Nota Final</th><td id="detalle_nota_final"></td></tr>
            <tr><th>Fecha Llamada</th><td id="detalle_fecha_llamada"></td></tr>
            <tr><th>Fecha Monitoreo</th><td id="detalle_fecha_monitoreo"></td></tr>
            <tr><th>Estado</th><td id="detalle_estado"></td></tr>
            <tr><th>Realizado por</th><td id="detalle_realizado_por"></td></tr>
        </table>
        <div id="detalle_criterios"></div>
        <div class="form-group">
            <label for="detalle_descripcion">Descripcion llamada:</label>
            <textarea id="detalle_descripcion" class="form-control" rows="2" readonly></textarea>
        </div>
        <div class="form-group">
            <label for="detalle_positivos">Aspectos positivos:</label>
            <textarea id="detalle_positivos" class="form-control" rows="2" readonly></textarea>
        </div>
        <div class="form-group">
            <label for="detalle_mejorar">Aspectos a mejorar:</label>
            <textarea id="detalle_mejorar" class="form-control" rows="2" readonly></textarea>
        </div> 
        <div class="form-group">
            <label for="detalle_observacion_final">Comentario refute:</label>
            <textarea id="detalle_observacion_final" class="form-control" rows="2" readonly></textarea>
        </div>
        <div class="form-group mt-3">
            <label for="comentario_agente">Comentario:</label>
            <textarea id="comentario_agente" class="form-control"></textarea >
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" id="btnAprobar" class="btn btn-success" style="display:none;">Aprobar</button>
        <button type="button" id="btnRefutar" class="btn btn-danger" style="display:none;">Refutar</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- JS y DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php require_once "../agente/vistas/parte_inferior.php"; ?>
