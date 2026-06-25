<?php require_once "vistas/parte_superior.php"; ?>
<div class="container">
    <h2>Canales y sus criterios/ítems asociados</h2>
    <?php
    include_once 'bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    // Traer todos los canales
    $consulta_canales = "SELECT id, nombre FROM canales";
    $resultado_canales = $conexion->prepare($consulta_canales);
    $resultado_canales->execute();
    $canales = $resultado_canales->fetchAll(PDO::FETCH_ASSOC);

    foreach($canales as $canal):
        // Traer criterios asociados a este canal
        $consulta_criterios = "
            SELECT DISTINCT c.id, c.nombre
            FROM asociacion_items ai
            JOIN criterios c ON ai.criterio_id = c.id
            WHERE ai.canal_id = ?
        ";
        $stmt_criterios = $conexion->prepare($consulta_criterios);
        $stmt_criterios->execute([$canal['id']]);
        $criterios = $stmt_criterios->fetchAll(PDO::FETCH_ASSOC);
    ?>
        <div class="card mb-3">
            <div class="card-header">
                <strong><?php echo htmlspecialchars($canal['nombre']); ?></strong>
            </div>
            <div class="card-body">
                <?php if(count($criterios) > 0): ?>
                    <?php foreach($criterios as $criterio): ?>
                        <h5><?php echo htmlspecialchars($criterio['nombre']); ?></h5>
                        <ul>
                            <?php
                            // Traer ítems asociados a este canal y criterio
                            $consulta_items = "
                                SELECT i.nombre
                                FROM asociacion_items ai
                                JOIN items i ON ai.item_id = i.id
                                WHERE ai.canal_id = ? AND ai.criterio_id = ?
                            ";
                            $stmt_items = $conexion->prepare($consulta_items);
                            $stmt_items->execute([$canal['id'], $criterio['id']]);
                            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                            foreach($items as $item):
                            ?>
                                <li><?php echo htmlspecialchars($item['nombre']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                <?php else: ?>
                    <em>No tiene criterios ni ítems asociados.</em>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once "vistas/parte_inferior.php"; ?>