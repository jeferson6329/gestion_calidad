<?php require_once "vistas/parte_superior.php" ?>
<div class="container">
    <h2>Asociar valores de ítems a un canal</h2>
    <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success">¡Valores guardados correctamente!</div>
    <?php endif; ?>

    <?php
    include_once 'bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    // Traer canales
    $consulta_canales = "SELECT id, nombre FROM canales";
    $resultado_canales = $conexion->prepare($consulta_canales);
    $resultado_canales->execute();
    $canales = $resultado_canales->fetchAll(PDO::FETCH_ASSOC);

    // Canal seleccionado
    $canal_id_seleccionado = isset($_POST['canal_id']) ? $_POST['canal_id'] : null;
    $valores_asociados = [];
    if ($canal_id_seleccionado) {
        $consulta_valores = "SELECT item_id, valor FROM canal_item_valor WHERE canal_id = ?";
        $resultado_valores = $conexion->prepare($consulta_valores);
        $resultado_valores->execute([$canal_id_seleccionado]);
        foreach ($resultado_valores->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $valores_asociados[$fila['item_id']] = $fila['valor'];
        }
    }

    // Criterios
    $consulta_criterios = "SELECT id, nombre FROM criterios";
    $stmt_criterios = $conexion->prepare($consulta_criterios);
    $stmt_criterios->execute();
    $criterios = $stmt_criterios->fetchAll(PDO::FETCH_ASSOC);

    // Ítems por criterio
    foreach ($criterios as &$criterio) {
        $consulta_items = "
            SELECT di.id, di.nombre
            FROM detalle_item di
            WHERE di.canal_id = ? AND di.criterio_id = ?
        ";
        $stmt_items = $conexion->prepare($consulta_items);
        $stmt_items->execute([$canal_id_seleccionado, $criterio['id']]);
        $criterio['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }

    // Pesos de criterios (convertidos a %)
    $pesos_criterios = [];
    if ($canal_id_seleccionado) {
        $consulta_pesos = "SELECT criterio_id, peso FROM canal_criterio_peso WHERE canal_id = ?";
        $stmt_pesos = $conexion->prepare($consulta_pesos);
        $stmt_pesos->execute([$canal_id_seleccionado]);
        foreach ($stmt_pesos->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $pesos_criterios[$fila['criterio_id']] = $fila['peso'] * 100;
        }
    }

    unset($criterio);
    ?>

    <form action="asociar_valores.php" method="POST">
        <div class="form-group">
            <label for="canal_id">Canal:</label>
            <select class="form-control" id="canal_id" name="canal_id" required onchange="this.form.submit()">
                <option value="">Seleccione un canal</option>
                <?php foreach($canales as $canal): ?>
                    <option value="<?php echo $canal['id']; ?>" <?php if($canal_id_seleccionado == $canal['id']) echo 'selected'; ?>>
                        <?php echo $canal['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($canal_id_seleccionado): ?>
        <form action="guardar_valores.php" method="POST">
            <input type="hidden" name="canal_id" value="<?php echo $canal_id_seleccionado; ?>">
            <?php foreach($criterios as $criterio): ?>
                <?php if (!empty($criterio['items'])): ?>
                    <div class="card mb-2">
                        <div class="card-header font-weight-bold">
                            <?php echo $criterio['nombre']; ?>
                            <?php if (isset($pesos_criterios[$criterio['id']])): ?>
                                <span class="badge badge-info ml-2">Peso: <?php echo $pesos_criterios[$criterio['id']]; ?>%</span>
                            <?php endif; ?>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php
                            $num_items = count($criterio['items']);
                            $valor_por_item = 0;
                            $usar_valor_automatico = false;

                            if (isset($pesos_criterios[$criterio['id']]) && $num_items > 0) {
                                $valor_por_item = round($pesos_criterios[$criterio['id']] / $num_items, 2);
                                $usar_valor_automatico = true;
                            }
                            ?>
                            <?php foreach($criterio['items'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $item['nombre']; ?>
                                    <input type="number"
                                        class="form-control ml-2"
                                        style="width:100px;display:inline-block"
                                        name="valores[<?php echo $item['id']; ?>]"
                                        min="0" max="100" step="0.01"
                                        value="<?php echo $usar_valor_automatico ? $valor_por_item : (isset($valores_asociados[$item['id']]) ? $valores_asociados[$item['id']] : ''); ?>"
                                        <?php echo $usar_valor_automatico ? 'readonly' : ''; ?>
                                    >
                                    <input type="hidden" name="nombres[<?php echo $item['id']; ?>]" value="<?php echo $item['nombre']; ?>">
                                    <input type="hidden" name="criterios[<?php echo $item['id']; ?>]" value="<?php echo $criterio['id']; ?>">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Guardar valores</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once "vistas/parte_inferior.php" ?>
