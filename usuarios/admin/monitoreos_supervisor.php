<?php

require_once "vistas/parte_superior.php";
include_once "bd/conexion.php";
$objeto = new Conexion();
$conexion = $objeto->Conectar();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tipo_usuario = $_SESSION['tipo_usuario'] ?? null;
$usuario_id = $_SESSION['usuario_id'] ?? null;

// Consulta monitoreos en revision
$consulta = "SELECT m.id, m.id_llamada, m.nombre_asesor, tm.nombre AS tipo_monitoreo, c.nombre AS canal, 
    m.ecn, m.ecuf, m.ecc, m.enc, m.nota_final, m.fecha_llamada, m.fecha_monitoreo, m.descripcion, 
    m.aspectos_positivos, m.aspectos_mejorar, m.comentario_agente, m.realizado_por, m.estado, 
    m.nivel_tipificacion, m.comentario_supervisor, p.nombre AS nombre_agente
    FROM monitoreos m
    LEFT JOIN tmonitoreo tm ON m.tipo_monitoreo = tm.id
    LEFT JOIN canales c ON m.canal_id = c.id
    LEFT JOIN personas p ON m.asesor_id = p.id
    WHERE m.estado = 'en revision'" . ($tipo_usuario === 'supervisor' ? " AND p.supervisor = ?" : "") . "
    ORDER BY m.id DESC";

if ($tipo_usuario === 'supervisor') {
    $resultado = $conexion->prepare($consulta);
    $resultado->execute([$usuario_id]);
} else {
    $resultado = $conexion->prepare($consulta);
    $resultado->execute();
}
$data = $resultado->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="mb-3">Monitoreos en Revisión de tus Agentes</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="text-center">
                <tr>
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
                <tr>
                    <td><?= htmlspecialchars($dat['id_llamada']) ?></td>
                    <td><?= htmlspecialchars($dat['tipo_monitoreo']) ?></td>
                    <td><?= htmlspecialchars($dat['canal']) ?></td>
                    <td><?= htmlspecialchars($dat['ecn']) ?></td>
                    <td><?= htmlspecialchars($dat['ecuf']) ?></td>
                    <td><?= htmlspecialchars($dat['ecc']) ?></td>
                    <td><?= htmlspecialchars($dat['enc']) ?></td>
                    <td><?= htmlspecialchars($dat['nota_final']) ?></td>
                    <td><?= htmlspecialchars($dat['fecha_llamada']) ?></td>
                    <td><?= htmlspecialchars($dat['fecha_monitoreo']) ?></td>
                    <td><span class="badge badge-warning">En Revisión</span></td>
                    <td><?= htmlspecialchars($dat['realizado_por']) ?></td>
                    <td>
                        <button class="btn btn-info btn-sm btnVerDetalles"
                            data-monitoreo='<?= json_encode($dat, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>Ver detalles</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detalle del Monitoreo -->
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
            <tr><th>Nivel de Tipificación</th><td id="detalle_nivel_tipificacion"></td></tr>
            <tr><th>ECN</th><td id="detalle_ecn"></td></tr>
            <tr><th>ECUF</th><td id="detalle_ecuf"></td></tr>
            <tr><th>ECC</th><td id="detalle_ecc"></td></tr>
            <tr><th>ENC</th><td id="detalle_enc"></td></tr>
            <tr><th>Nota Final</th><td id="detalle_nota_final"></td></tr>
            <tr><th>Fecha Llamada</th><td id="detalle_fecha_llamada"></td></tr>
            <tr><th>Fecha Monitoreo</th><td id="detalle_fecha_monitoreo"></td></tr>
            <tr><th>Estado</th><td id="detalle_estado"></td></tr>
            <tr><th>Realizado por</th><td id="detalle_realizado_por"></td></tr>
        </table>
        <div id="detalle_criterios"></div>
        <div class="form-group">
            <label for="detalle_descripcion">Descripción llamada:</label>
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
            <label for="detalle_observacion_agente">Comentario refute del agente:</label>
            <textarea id="detalle_observacion_agente" class="form-control" rows="2" readonly></textarea>
        </div>
        <div class="form-group mt-3">
            <label for="comentario_supervisor">Comentario supervisor:</label>
            <textarea id="comentario_supervisor" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnAprobar" class="btn btn-success">Aprobar refute</button>
        <button type="button" id="btnRefutar" class="btn btn-danger">Rechazar refute</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let monitoreoActual = null;

document.querySelectorAll('.btnVerDetalles').forEach(btn => {
    btn.addEventListener('click', function() {
        monitoreoActual = JSON.parse(this.getAttribute('data-monitoreo'));
        document.getElementById('detalle_id_llamada').textContent = monitoreoActual.id_llamada ?? '';
        document.getElementById('detalle_nombre_asesor').textContent = monitoreoActual.nombre_asesor ?? monitoreoActual.nombre_agente ?? '';
        document.getElementById('detalle_tipo_monitoreo').textContent = monitoreoActual.tipo_monitoreo ?? '';
        document.getElementById('detalle_canal').textContent = monitoreoActual.canal ?? '';
        document.getElementById('detalle_nivel_tipificacion').textContent = monitoreoActual.nivel_tipificacion ?? '';
        document.getElementById('detalle_ecn').textContent = monitoreoActual.ecn ?? '';
        document.getElementById('detalle_ecuf').textContent = monitoreoActual.ecuf ?? '';
        document.getElementById('detalle_ecc').textContent = monitoreoActual.ecc ?? '';
        document.getElementById('detalle_enc').textContent = monitoreoActual.enc ?? '';
        document.getElementById('detalle_nota_final').textContent = monitoreoActual.nota_final ?? '';
        document.getElementById('detalle_fecha_llamada').textContent = monitoreoActual.fecha_llamada ?? '';
        document.getElementById('detalle_fecha_monitoreo').textContent = monitoreoActual.fecha_monitoreo ?? '';
        document.getElementById('detalle_estado').textContent = monitoreoActual.estado ?? '';
        document.getElementById('detalle_realizado_por').textContent = monitoreoActual.realizado_por ?? '';
        document.getElementById('detalle_descripcion').value = monitoreoActual.descripcion ?? '';
        document.getElementById('detalle_positivos').value = monitoreoActual.aspectos_positivos ?? '';
        document.getElementById('detalle_mejorar').value = monitoreoActual.aspectos_mejorar ?? '';
        document.getElementById('detalle_observacion_agente').value = monitoreoActual.comentario_agente ?? '';
        document.getElementById('comentario_supervisor').value = monitoreoActual.comentario_supervisor ?? '';
        $('#modalDetalle').modal('show');
    });
});

// Botón aprobar
document.getElementById('btnAprobar').addEventListener('click', function() {
    if (!monitoreoActual) return;
    const comentarioSupervisor = document.getElementById('comentario_supervisor').value;
    Swal.fire({
        title: '¿Aprobar refute?',
        text: 'El monitoreo pasará a estado "refutado".',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed) {
            fetch('aprobar_refute.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_monitoreo=' + encodeURIComponent(monitoreoActual.id) +
                      '&comentario_supervisor=' + encodeURIComponent(comentarioSupervisor)
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Aprobado', 'El refute fue aprobado y el monitoreo actualizado.', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', 'No se pudo aprobar el refute.', 'error');
                }
            });
        }
    });
});

// Botón rechazar
document.getElementById('btnRefutar').addEventListener('click', function() {
    if (!monitoreoActual) return;
    const comentarioSupervisor = document.getElementById('comentario_supervisor').value;
    Swal.fire({
        title: '¿Rechazar refute?',
        input: 'textarea',
        inputLabel: 'Razones del rechazo',
        inputPlaceholder: 'Escribe las razones aquí...',
        showCancelButton: true,
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) return 'Debes escribir una razón';
        }
    }).then((result) => {
        if(result.isConfirmed) {
            fetch('rechazar_refute.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_monitoreo=' + encodeURIComponent(monitoreoActual.id) +
                      '&razones=' + encodeURIComponent(result.value) +
                      '&comentario_supervisor=' + encodeURIComponent(comentarioSupervisor)
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Refute rechazado', 'El agente será notificado con las razones.', 'info')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', 'No se pudo rechazar el refute.', 'error');
                }
            });
        }
    });
});
</script>
<?php require_once "vistas/parte_inferior.php"; ?>