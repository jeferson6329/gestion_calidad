<?php
// filepath: c:\xampp\htdocs\Gestion_calidad\usuarios\lider_calidad\reevaluar_monitoreo.php
require_once "vistas/parte_superior.php";
include_once 'bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Trae todos los datos principales del monitoreo
$stmt = $conexion->prepare("SELECT * FROM monitoreos WHERE id = ?");
$stmt->execute([$id]);
$monitoreo = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$monitoreo){
    echo "<div class='alert alert-danger'>Monitoreo no encontrado</div>";
    require_once "vistas/parte_inferior.php";
    exit;
}

$asesor_nombre = $monitoreo['nombre_asesor'];

$stmt_tipo = $conexion->prepare("SELECT nombre FROM tmonitoreo WHERE id = ?");
$stmt_tipo->execute([$monitoreo['tipo_monitoreo']]);
$tipo_monitoreo_nombre = $stmt_tipo->fetchColumn();

$stmt_canal = $conexion->prepare("SELECT nombre FROM canales WHERE id = ?");
$stmt_canal->execute([$monitoreo['canal_id']]);
$canal_nombre = $stmt_canal->fetchColumn();

$stmt_criterios = $conexion->prepare("
    SELECT c.id, c.nombre, ccp.peso
    FROM criterios c
    INNER JOIN canal_criterio_peso ccp ON c.id = ccp.criterio_id
    WHERE ccp.canal_id = ?
    ORDER BY c.id
");
$stmt_criterios->execute([$monitoreo['canal_id']]);
$criterios = $stmt_criterios->fetchAll(PDO::FETCH_ASSOC);

// Identificar IDs de ECN, ECUF, ECC, ENC
$id_criterio1 = $id_criterio2 = $id_criterio3 = $id_criterio4 = null;
foreach($criterios as $crit) {
    $nombre = strtoupper($crit['nombre']);
    if(strpos($nombre, 'ECN') !== false) $id_criterio1 = $crit['id'];
    if(strpos($nombre, 'ECUF') !== false) $id_criterio2 = $crit['id'];
    if(strpos($nombre, 'ECC') !== false) $id_criterio3 = $crit['id'];
    if(strpos($nombre, 'ENC') !== false) $id_criterio4 = $crit['id'];
}

// Traer items de cada criterio
foreach($criterios as &$criterio){
    $stmt_items = $conexion->prepare("
        SELECT mi.item_id, i.nombre, mi.cumple, civ.valor
        FROM monitoreo_items mi
        JOIN items i ON mi.item_id = i.id
        LEFT JOIN canal_item_valor civ ON civ.canal_id = ? AND civ.item_id = i.id
        WHERE mi.monitoreo_id = ? AND i.criterio_id = ?
    ");
    $stmt_items->execute([$monitoreo['canal_id'], $id, $criterio['id']]);
    $criterio['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
}
unset($criterio);
?>

<div class="container">
    <h2>Reevaluar Monitoreo</h2>
    <form id="formReevaluar" action="bd/guardar_reevaluacion.php" method="POST">
        <input type="hidden" name="id_monitoreo" value="<?php echo $id; ?>">
        <input type="hidden" name="estado" value="reevaluado">

        <!-- Asesor e ID Llamada -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Asesor:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($asesor_nombre); ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label>ID Llamada:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($monitoreo['id_llamada']); ?>" readonly>
            </div>
        </div>

        <!-- Tipo de monitoreo y Canal -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Tipo de monitoreo:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($tipo_monitoreo_nombre); ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label>Canal:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($canal_nombre); ?>" readonly>
            </div>
        </div>

        <!-- Nivel de tipificación -->
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Nivel de tipificación:</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($monitoreo['nivel_tipificacion']); ?>" readonly>
    </div>
    <div class="form-group col-md-6">
        <label>Nivel de tipificación 2:</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($monitoreo['nivel_tipificacion2']); ?>" readonly>
    </div>
</div>

        <!-- Fecha de llamada y monitoreo -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Fecha de llamada:</label>
                <input type="date" class="form-control" name="fecha_llamada" readonly value="<?php echo $monitoreo['fecha_llamada']; ?>">
            </div>
            <div class="form-group col-md-6">
                <label>Fecha de monitoreo:</label>
                <input type="date" class="form-control" name="fecha_monitoreo" readonly value="<?php echo $monitoreo['fecha_monitoreo']; ?>">
            </div>
        </div>

        <!-- Errores Críticos de Cumplimiento -->
        <div class="form-group">
            <label>Errores Críticos de Cumplimiento</label>
            <select class="form-control" name="error_critico" disabled>
                <option value="Sí" <?php echo ($monitoreo['error_critico'] == "Sí") ? "selected" : ""; ?>>Sí</option>
                <option value="No" <?php echo ($monitoreo['error_critico'] == "No") ? "selected" : ""; ?>>No</option>
            </select>
        </div>

        <!-- Criterios e ítems -->
        <?php foreach($criterios as $criterio): ?>
            <div class="card mb-2">
                <div class="card-header font-weight-bold"><?php echo htmlspecialchars($criterio['nombre']); ?></div>
                <ul class="list-group list-group-flush">
                    <?php foreach($criterio['items'] as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($item['nombre']); ?></span>
                            <select class="form-control cumple-select" style="width:120px; display:inline-block;"
                                    name="cumple[<?php echo $item['item_id']; ?>]"
                                    data-valor="<?php echo ($item['valor'] !== null ? $item['valor'] : 0); ?>"
                                    data-criterio="<?php echo $criterio['id']; ?>" required>
                                <option value="1" <?php if($item['cumple'] == 1) echo 'selected'; ?>>Sí</option>
                                <option value="0" <?php if($item['cumple'] == 0) echo 'selected'; ?>>No</option>
                            </select>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="card-footer text-muted">Peso: <?php echo ($criterio['peso']*100); ?>%</div>
            </div>
        <?php endforeach; ?>

        <!-- ECN, ECUF, ECC, ENC -->
        <div class="row" style="display: none;">
            <div class="col-md-3"><label>ECN:</label><input type="number" class="form-control" id="criterio1" name="criterio1" value="0" readonly></div>
            <div class="col-md-3"><label>ECUF:</label><input type="number" class="form-control" id="criterio2" name="criterio2" value="0" readonly></div>
            <div class="col-md-3"><label>ECC:</label><input type="number" class="form-control" id="criterio3" name="criterio3" value="0" readonly></div>
            <div class="col-md-3"><label>ENC:</label><input type="number" class="form-control" id="criterio4" name="criterio4" value="0" readonly></div>
        </div>

        <!-- Nota final -->
        <div class="form-group">
            <label>Nota final:</label>
            <input type="number" class="form-control" id="valor_total" name="nota_final" readonly>
        </div>

        <!-- Comentarios -->
        <div class="form-group">
            <label>Descripción de la llamada:</label>
            <textarea class="form-control" name="descripcion" rows="2"><?php echo htmlspecialchars($monitoreo['descripcion']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Aspectos positivos:</label>
            <textarea class="form-control" name="aspectos_positivos" rows="2"><?php echo htmlspecialchars($monitoreo['aspectos_positivos']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Aspectos a mejorar:</label>
            <textarea class="form-control" name="aspectos_mejorar" rows="2"><?php echo htmlspecialchars($monitoreo['aspectos_mejorar']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Comentario del agente:</label>
            <textarea class="form-control" readonly rows="2"><?php echo htmlspecialchars($monitoreo['comentario_agente']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Comentario del supervisor:</label>
            <textarea class="form-control" readonly rows="2"><?php echo htmlspecialchars($monitoreo['comentario_supervisor']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Comentario de refutación:</label>
            <textarea class="form-control" name="comentario_refute" rows="2"><?php echo htmlspecialchars($monitoreo['comentario_refute']); ?></textarea>
        </div>

        <div class="form-group text-right">
            <button type="submit" class="btn btn-warning">Reevaluar</button>
            <a href="monitoreos.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
function recalcularTotal(){
    let criterios={}, pesos={}, sumaItems={}, algunNo={};

    <?php foreach($criterios as $crit): ?>
        criterios[<?php echo $crit['id']; ?>]=0;
        pesos[<?php echo $crit['id']; ?>]=<?php echo floatval($crit['peso']); ?>;
        sumaItems[<?php echo $crit['id']; ?>]=0;
        algunNo[<?php echo $crit['id']; ?>]=false;
    <?php endforeach; ?>

    $('.cumple-select').each(function(){
        let cumple = $(this).val();
        let valor = parseFloat($(this).data('valor')) || 0;
        let criterio_id = $(this).data('criterio');

        sumaItems[criterio_id] += valor;
        if(cumple === "1"){
            criterios[criterio_id] += valor;
        } else if(cumple === "0"){
            algunNo[criterio_id] = true;
        }
    });

    let total = 0;
    let index = 1; // posición de criterio (ECN=1, ECUF=2, ECC=3, ENC=4)

    for(let id in criterios){
        let totalCumplido = criterios[id];
        let totalPosible = sumaItems[id];
        let peso = pesos[id];
        let porcentaje = 0;

        // 🚨 Regla especial: si es el 3er criterio y tiene algún "No", anula todo
        if(index === 2,3 && algunNo[id]){
            porcentaje = 0;
            // OJO: aquí no sumamos nada al total
        } else {
            porcentaje = (totalPosible > 0) ? (totalCumplido / totalPosible) : 0;
            total += porcentaje * (peso * 100);
        }

        // Actualizar los inputs ocultos según posición
        if(index === 1) $('#criterio1').val((porcentaje*100).toFixed(2));
        if(index === 2) $('#criterio2').val((porcentaje*100).toFixed(2));
        if(index === 3) $('#criterio3').val((porcentaje*100).toFixed(2));
        if(index === 4) $('#criterio4').val((porcentaje*100).toFixed(2));

        index++;
    }

    $('#valor_total').val(total.toFixed(2));
}



    recalcularTotal();
    $('.cumple-select').change(recalcularTotal);

    $('#formReevaluar').on('submit', function(e){
        e.preventDefault();
        recalcularTotal();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp){
                if(resp.success){
                    Swal.fire({
                        icon: 'success',
                        title: 'Reevaluación guardada',
                        text: resp.message,
                        confirmButtonText: 'Aceptar',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = resp.redirect;
                    });
                } else {
                    Swal.fire('Error', resp.message, 'error');
                }
            },
            error: function(xhr){
    // intentar leer JSON; si no hay JSON, mostrar responseText crudo
    let serverMsg = '';
    try {
        let json = JSON.parse(xhr.responseText);
        serverMsg = json.message || xhr.responseText;
    } catch (err) {
        serverMsg = xhr.responseText || 'Sin respuesta del servidor';
    }
    Swal.fire({
        icon: 'error',
        title: 'Error',
        html: 'Ocurrió un problema al guardar.<br><pre style="text-align:left;">' + $('<div/>').text(serverMsg).html() + '</pre>'
    });
}

        });
    });
});
</script>

<?php require_once "vistas/parte_inferior.php"; ?>
