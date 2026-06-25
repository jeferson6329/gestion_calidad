<?php require_once "vistas/parte_superior.php" ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="container">
    <h1>Monitoreos de la SDM</h1>

    <?php
    include_once 'bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    // Filtrar por estado si se recibe por GET
    $filtros = [];
    $parametros = [];

    if (isset($_GET['estado']) && in_array($_GET['estado'], ['pendiente','aprobado','refutado','reevaluado', 'en revision'])) {
        $filtros[] = "m.estado = ?";
        $parametros[] = $_GET['estado'];
    }

    if (isset($_GET['fecha_desde']) && isset($_GET['fecha_hasta'])) {
        $filtros[] = "m.fecha_monitoreo BETWEEN ? AND ?";
        $parametros[] = $_GET['fecha_desde'];
        $parametros[] = $_GET['fecha_hasta'];
    }

    $where = count($filtros) > 0 ? ' WHERE ' . implode(' AND ', $filtros) : '';

    // Totales por estado
    $estados = ['pendiente', 'aprobado', 'refutado', 'reevaluado', 'en revision'];
    $totales_estado = [];
    foreach($estados as $estado){
        $consulta = "SELECT COUNT(*) AS total FROM monitoreos WHERE estado = ?";
        $stmt = $conexion->prepare($consulta);
        $stmt->execute([$estado]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $totales_estado[$estado] = $fila['total'];
    }

    // Consulta principal
    $consulta = "SELECT m.id, m.id_llamada, m.nombre_asesor, tm.nombre AS tipo_monitoreo, c.nombre AS canal, 
        m.criterio1, m.criterio2, m.criterio3, m.criterio4, m.nota_final, m.fecha_llamada, m.fecha_monitoreo, 
        m.descripcion, m.aspectos_positivos, m.aspectos_mejorar, m.comentario_agente, m.comentario_supervisor, m.comentario_refute, 
        m.estado, m.realizado_por,
        p_agente.supervisor AS supervisor,
        m.nivel_tipificacion
        FROM monitoreos m
        LEFT JOIN tmonitoreo tm ON m.tipo_monitoreo = tm.id
        LEFT JOIN canales c ON m.canal_id = c.id
        LEFT JOIN personas p_agente ON m.asesor_id = p_agente.id
        $where
        ORDER BY m.id DESC";
    $resultado = $conexion->prepare($consulta);
    $resultado->execute($parametros);
    $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Botones de estado -->
    <div class="row mb-3">
        <div class="col-auto"><a href="monitoreos.php" class="btn btn-secondary">Ver todos <span class="badge badge-light"><?php echo array_sum($totales_estado); ?></span></a></div>
        <?php foreach($estados as $estado): ?>
            <div class="col-auto">
                <a href="monitoreos.php?estado=<?php echo $estado ?>" class="btn <?php echo $estado == 'pendiente' ? 'btn-secondary' : ($estado=='aprobado'?'btn-success':($estado=='refutado'?'btn-danger':($estado=='reevaluado'?'btn-info':'btn-warning'))) ?>">
                    <?php echo ucfirst($estado) ?> <span class="badge badge-light"><?php echo $totales_estado[$estado]; ?></span>
                </a>
            </div>
        <?php endforeach; ?>
        <div class="col-auto"><a href="crear_monitoreo.php" class="btn btn-primary">Nuevo monitoreo</a></div>
    </div>

    <!-- Filtros avanzados -->
    <div class="row mb-2">
        <div class="col-md-3 d-flex align-items-center">
            <select id="filtroCampo" class="form-control me-2">
                <option value="">Filtrar por...</option>
                <option value="1">Nombre del agente</option>
                <option value="2">Tipo de monitoreo</option>
                <option value="3">Canal</option>
                <option value="10">Fecha monitoreo</option>
            </select>
            <button id="btnExportarExcel" class="btn btn-success ms-2"><i class="fas fa-file-excel"></i> Exportar Excel</button>
        </div>
        <div class="col-md-4" id="contenedorFiltroOpciones"></div>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
        <table id="tablamonitoreos" class="table table-striped table-bordered table-condensed" style="width:100%">
            <thead class="text-center">
                <tr>
                    <th>Id</th>
                    <th>Id llamada</th>
                    <th>Nombre Asesor</th>
                    <th>Tipo de monitoreo</th>
                    <th>Canal</th>
                    <th>ECN</th>
                    <th>ECUF</th>
                    <th>ECC</th>
                    <th>ENC</th>
                    <th>Nota final</th>
                    <th>Fecha llamada</th>
                    <th>Fecha monitoreo</th>
                    <th style="display:none;">Descripción</th>
                    <th>Estado</th>
                    <th>Realizado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($data as $dat): ?>
                <tr>
                    <td><?php echo $dat['id'] ?></td>
                    <td><?php echo $dat['id_llamada'] ?></td>
                    <td><?php echo $dat['nombre_asesor'] ?></td>
                    <td><?php echo $dat['tipo_monitoreo'] ?></td>
                    <td><?php echo $dat['canal'] ?></td>
                    <td><?php echo $dat['criterio1'] ?></td>
                    <td><?php echo $dat['criterio2'] ?></td>
                    <td><?php echo $dat['criterio3'] ?></td>
                    <td><?php echo $dat['criterio4'] ?></td>
                    <td><?php echo $dat['nota_final'] ?></td>
                    <td><?php echo $dat['fecha_llamada'] ?></td>
                    <td><?php echo $dat['fecha_monitoreo'] ?></td>
                    <td style="display:none;"><?php echo $dat['descripcion'] ?></td>
                    <td>
                        <?php 
                        switch($dat['estado']){
                            case 'pendiente': echo '<span class="badge badge-secondary">Pendiente</span>'; break;
                            case 'aprobado': echo '<span class="badge badge-success">Aprobado</span>'; break;
                            case 'refutado': echo '<span class="badge badge-danger">Refutado</span>'; break;
                            case 'reevaluado': echo '<span class="badge badge-info">Re-evaluado</span>'; break;
                            default: echo $dat['estado'];
                        }
                        ?>
                    </td>
                    <td><?php echo $dat['realizado_por'] ?></td>
                    <td class="text-center">
                        <a href="editar_monitoreo.php?id=<?php echo $dat['id'] ?>" class="btn btn-info btn-sm">Editar</a>
                        <a href="#" class="btn btn-warning btn-sm btneditarmonitoreo" data-id="<?php echo $dat['id'] ?>">Reevaluar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Convertimos arrays PHP a JS
var datosExcel = <?php echo json_encode($data); ?>;
var agentesUnicos = <?php echo json_encode(array_values(array_unique(array_column($data, 'nombre_asesor')))); ?>;
var tiposUnicos = <?php echo json_encode(array_values(array_unique(array_column($data, 'tipo_monitoreo')))); ?>;
var canalesUnicos = <?php echo json_encode(array_values(array_unique(array_column($data, 'canal')))); ?>;

// Filtros activos
let filtro = {
    agente: '',
    tipo: '',
    canal: '',
    fechaInicio: '',
    fechaFin: ''
};

// Renderizar selects según filtro elegido
document.getElementById('filtroCampo').addEventListener('change', function() {
    var tipo = this.value;
    var html = '';

    if(tipo === "1"){ // Agente
        html = '<select id="filtroNombreAgente" class="form-control"><option value="">Todos</option>';
        agentesUnicos.forEach(a => html += `<option value="${a}">${a}</option>`);
        html += '</select>';
    } else if(tipo === "2"){ // Tipo monitoreo
        html = '<select id="filtroTipoMonitoreo" class="form-control"><option value="">Todos</option>';
        tiposUnicos.forEach(t => html += `<option value="${t}">${t}</option>`);
        html += '</select>';
    } else if(tipo === "3"){ // Canal
        html = '<select id="filtroCanal" class="form-control"><option value="">Todos</option>';
        canalesUnicos.forEach(c => html += `<option value="${c}">${c}</option>`);
        html += '</select>';
    } else if(tipo === "10"){ // Fechas
        html = `
        <div class="d-flex align-items-center gap-2">
            <div class="d-flex flex-column me-2">
                <label for="filtroFechaInicio" class="form-label mb-1">Desde:</label>
                <input type="date" id="filtroFechaInicio" class="form-control">
            </div>
            <div class="d-flex flex-column">
                <label for="filtroFechaFin" class="form-label mb-1">Hasta:</label>
                <input type="date" id="filtroFechaFin" class="form-control">
            </div>
        </div>`;
    }

    document.getElementById('contenedorFiltroOpciones').innerHTML = html;
});

// Actualizar filtros y tabla
document.addEventListener('change', function(e){
    if(e.target.id === 'filtroNombreAgente') filtro.agente = e.target.value;
    if(e.target.id === 'filtroTipoMonitoreo') filtro.tipo = e.target.value;
    if(e.target.id === 'filtroCanal') filtro.canal = e.target.value;
    if(e.target.id === 'filtroFechaInicio') filtro.fechaInicio = e.target.value;
    if(e.target.id === 'filtroFechaFin') filtro.fechaFin = e.target.value;

    filtrarTabla();
});

// Función filtrar tabla
function filtrarTabla(){
    let rows = document.querySelectorAll('#tablamonitoreos tbody tr');
    rows.forEach(row => {
        let tds = row.querySelectorAll('td');
        let mostrar = true;
        let nombreAgente = tds[2].textContent.trim();
        let tipoMonitoreo = tds[3].textContent.trim();
        let canal = tds[4].textContent.trim();
        let fechaMonitoreo = tds[11].textContent.trim();

        if(filtro.agente && nombreAgente !== filtro.agente) mostrar = false;
        if(filtro.tipo && tipoMonitoreo !== filtro.tipo) mostrar = false;
        if(filtro.canal && canal !== filtro.canal) mostrar = false;
        if(filtro.fechaInicio && fechaMonitoreo < filtro.fechaInicio) mostrar = false;
        if(filtro.fechaFin && fechaMonitoreo > filtro.fechaFin) mostrar = false;

        row.style.display = mostrar ? '' : 'none';
    });
}

// Exportar Excel
document.getElementById('btnExportarExcel').addEventListener('click', function(){
    let rows = [
        ["Id llamada","Nombre del asesor","Tipo de monitoreo","Canal","ECN","ECUF","ECC","ENC","Nota final","Fecha llamada","Fecha monitoreo","Descripción","Aspectos positivos","Aspectos a mejorar","Supervisor","Realizado por","Nivel tipificación"]
    ];

    let filtrados = datosExcel.filter(row=>{
        if(filtro.agente && row.nombre_asesor!==filtro.agente) return false;
        if(filtro.tipo && row.tipo_monitoreo!==filtro.tipo) return false;
        if(filtro.canal && row.canal!==filtro.canal) return false;
        if(filtro.fechaInicio && row.fecha_monitoreo<filtro.fechaInicio) return false;
        if(filtro.fechaFin && row.fecha_monitoreo>filtro.fechaFin) return false;
        return true;
    });

    if(filtrados.length===0){ alert('No hay datos para exportar'); return; }

    filtrados.forEach(r=>{
        rows.push([
            r.id_llamada, r.nombre_asesor, r.tipo_monitoreo, r.canal, r.criterio1, r.criterio2, r.criterio3, r.criterio4,
            r.nota_final, r.fecha_llamada, r.fecha_monitoreo, r.descripcion, r.aspectos_positivos, r.aspectos_mejorar,
            r.supervisor??'', r.realizado_por, r.nivel_tipificacion??''
        ]);
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, "Monitoreos");

    // Nombre dinámico del archivo
    let hoy = new Date();
    let fechaActual = hoy.toISOString().split('T')[0];
    let nombreArchivo = "monitoreos";
    if(filtro.agente) nombreArchivo+=`_agente_${filtro.agente.replace(/ /g,'_')}_${fechaActual}`;
    else if(filtro.tipo) nombreArchivo+=`_tipo_${filtro.tipo.replace(/ /g,'_')}_${fechaActual}`;
    else if(filtro.canal) nombreArchivo+=`_canal_${filtro.canal.replace(/ /g,'_')}_${fechaActual}`;
    else if(filtro.fechaInicio && filtro.fechaFin) nombreArchivo+=`_desde_${filtro.fechaInicio}_hasta_${filtro.fechaFin}`;
    else nombreArchivo+=`_todos_${fechaActual}`;

    XLSX.writeFile(wb, `${nombreArchivo}.xlsx`);
});
</script>

<?php require_once "vistas/parte_inferior.php" ?>
