<?php
// filepath: c:\xampp\htdocs\Gestion_calidad\usuarios\admin\editar_detalle_item.php

require_once "vistas/parte_superior.php";
include_once '../admin/bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Obtener canal_id por GET o POST
$canal_id = isset($_GET['canal_id']) ? $_GET['canal_id'] : (isset($_POST['canal_id']) ? $_POST['canal_id'] : null);

if (!$canal_id) {
    // Traer canales para el selector
    $consulta_canales = "SELECT id, nombre FROM canales";
    $resultado_canales = $conexion->prepare($consulta_canales);
    $resultado_canales->execute();
    $canales = $resultado_canales->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="container">
        <h2>Selecciona una canal</h2>
        <form method="get" action="editar_detalle_item.php">
            <div class="form-group">
                <label for="canal_id">Canal:</label>
                <select class="form-control" name="canal_id" id="canal_id" required>
                    <option value="">Seleccione un canal</option>
                    <?php foreach($canales as $canal): ?>
                        <option value="<?php echo $canal['id']; ?>"><?php echo $canal['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Ver ítems</button>
        </form>
    </div>
    <?php
    require_once "vistas/parte_inferior.php";
    exit;
}

// Traer criterios únicos y ordenados
$consulta_criterios = "SELECT DISTINCT id, nombre FROM criterios ORDER BY id ASC";
$stmt_criterios = $conexion->prepare($consulta_criterios);
$stmt_criterios->execute();
$criterios = $stmt_criterios->fetchAll(PDO::FETCH_ASSOC);

// Traer ítems personalizados asociados a la campaña y criterio SIN duplicados
foreach($criterios as &$criterio){
    $consulta_items = "
        SELECT di.id, di.nombre
        FROM detalle_item di
        WHERE di.canal_id = ? AND di.criterio_id = ?
        GROUP BY di.id, di.nombre
        ORDER BY di.id ASC
    ";
    $stmt_items = $conexion->prepare($consulta_items);
    $stmt_items->execute([$canal_id, $criterio['id']]);
    $criterio['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
}
unset($criterio);


?>

<div class="container">
    <h2>Editar y agregar ítems para campaña</h2>
    <form action="guardar_detalle_item.php" method="POST">
        <input type="hidden" name="canal_id" value="<?php echo $canal_id; ?>">
        <?php foreach($criterios as $criterio): ?>
            <?php if (!empty($criterio['items']) || true): // Siempre muestra el bloque para agregar ?>
            <h4><?php echo $criterio['nombre']; ?></h4>
            <ul>
            <?php foreach($criterio['items'] as $item): ?>
                <li>
                    <input type="text" name="detalles[<?php echo $criterio['id']; ?>][<?php echo $item['id']; ?>]" value="<?php echo htmlspecialchars($item['nombre']); ?>" class="form-control" />
                </li>
            <?php endforeach; ?>
            <!-- Campo para agregar nuevo ítem -->
            <li>
                <input type="text" name="nuevo_item[<?php echo $criterio['id']; ?>]" placeholder="Nuevo ítem para <?php echo $criterio['nombre']; ?>" class="form-control" />
            </li>
            </ul>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-success">Guardar detalles</button>
    </form>
</div>

<?php require_once "vistas/parte_inferior.php"; ?>
