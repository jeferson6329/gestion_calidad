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
    <h2>Detalle Reevaluación de Monitoreo</h2>
    
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

    <!-- Fechas -->
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Fecha de llamada:</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($monitoreo['fecha_llamada']); ?>" readonly>
        </div>
        <div class="form-group col-md-6">
            <label>Fecha de monitoreo:</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($monitoreo['fecha_monitoreo']); ?>" readonly>
        </div>
    </div>

    <!-- Errores Críticos de Cumplimiento -->
    <div class="form-group">
        <label>Errores Críticos de Cumplimiento</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($monitoreo['error_critico']); ?>" readonly>
    </div>

    <!-- Criterios e ítems -->
    <?php foreach($criterios as $criterio): ?>
        <div class="card mb-2">
            <div class="card-header font-weight-bold"><?php echo htmlspecialchars($criterio['nombre']); ?></div>
            <ul class="list-group list-group-flush">
                <?php foreach($criterio['items'] as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars($item['nombre']); ?></span>
                        <span class="badge badge-<?php echo $item['cumple'] ? 'success' : 'danger'; ?>">
                            <?php echo $item['cumple'] ? 'Sí' : 'No'; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="card-footer text-muted">Peso: <?php echo ($criterio['peso']*100); ?>%</div>
        </div>
    <?php endforeach; ?>

    <!-- Nota final -->
    <div class="form-group">
        <label>Nota final:</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($monitoreo['nota_final']); ?>" readonly>
    </div>

    <!-- Comentarios -->
    <div class="form-group">
        <label>Descripción de la llamada:</label>
        <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($monitoreo['descripcion']); ?></textarea>
    </div>
    <div class="form-group">
        <label>Aspectos positivos:</label>
        <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($monitoreo['aspectos_positivos']); ?></textarea>
    </div>
    <div class="form-group">
        <label>Aspectos a mejorar:</label>
        <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($monitoreo['aspectos_mejorar']); ?></textarea>
    </div>
    <div class="form-group">
        <label>Comentario del agente:</label>
        <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($monitoreo['comentario_agente']); ?></textarea>
    </div>
    <div class="form-group">
        <label>Comentario del supervisor:</label>
        <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($monitoreo['comentario_supervisor']); ?></textarea>
    </div>
    <div class="form-group">
        <label>Comentario de refutación:</label>
        <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($monitoreo['comentario_refute']); ?></textarea>
    </div>

    <div class="form-group text-right">
        <a href="monitoreos.php" class="btn btn-secondary">Volver</a>
    </div>
</div>

<?php require_once "vistas/parte_inferior.php"; ?>
