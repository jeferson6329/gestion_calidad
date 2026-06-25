<?php require_once "vistas/parte_superior.php" ?>
<div class="container">
    <h2>Asignar pesos de criterios por canal</h2>
    <?php
    include_once 'bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    // Traer canales
    $consulta_canales = "SELECT id, nombre FROM canales";
    $resultado_canales = $conexion->prepare($consulta_canales);
    $resultado_canales->execute();
    $canales = $resultado_canales->fetchAll(PDO::FETCH_ASSOC);

    // Traer criterios
    $consulta_criterios = "SELECT id, nombre FROM criterios";
    $resultado_criterios = $conexion->prepare($consulta_criterios);
    $resultado_criterios->execute();
    $criterios = $resultado_criterios->fetchAll(PDO::FETCH_ASSOC);

    // Canal seleccionado
    $canal_id_seleccionado = isset($_POST['canal_id']) ? $_POST['canal_id'] : (isset($_GET['canal_id']) ? $_GET['canal_id'] : null);

    // Pesos actuales
    $pesos_asociados = [];
    if ($canal_id_seleccionado) {
        $consulta_pesos = "SELECT criterio_id, peso FROM canal_criterio_peso WHERE canal_id = ?";
        $resultado_pesos = $conexion->prepare($consulta_pesos);
        $resultado_pesos->execute([$canal_id_seleccionado]);
        foreach ($resultado_pesos->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $pesos_asociados[$fila['criterio_id']] = $fila['peso'];
        }
    }

    // Mensajes
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger">'.htmlspecialchars($_GET['error']).'</div>';
    }
    if (isset($_GET['exito'])) {
        echo '<div class="alert alert-success">'.htmlspecialchars($_GET['exito']).'</div>';
    }
    ?>
    
    <!-- Formulario para seleccionar canal -->
    <form action="asociar_pesos_criterios.php" method="POST">
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
    <!-- Formulario para asignar pesos -->
    <form action="guardar_pesos_criterios.php" method="POST" onsubmit="return validarSumaPesos();">
        <input type="hidden" name="canal_id" value="<?php echo $canal_id_seleccionado; ?>">
        
        <?php foreach($criterios as $criterio): ?>
            <div class="form-group">
                <label><?php echo $criterio['nombre']; ?> (Peso %):</label>
                <input type="number" class="form-control peso-input" name="pesos[<?php echo $criterio['id']; ?>]" 
                    value="<?php echo isset($pesos_asociados[$criterio['id']]) ? ($pesos_asociados[$criterio['id']] * 100) : ''; ?>" 
                    required step="any" min="0" max="100">
            </div>
        <?php endforeach; ?>

        <div class="form-group">
            <label><strong>Suma total:</strong> <span id="sumaPesos">0</span>%</label>
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>

    <!-- ✅ Script de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Calcula la suma total de los pesos en los inputs
    function calcularSumaPesos() {
        let suma = 0;
        document.querySelectorAll('.peso-input').forEach(function(input) {
            suma += parseFloat(input.value) || 0;
        });
        document.getElementById('sumaPesos').innerText = suma.toFixed(2);
        return suma;
    }

    // Valida que la suma sea exactamente 100%
    function validarSumaPesos() {
        let suma = calcularSumaPesos();
        if (suma !== 100) {
            Swal.fire({
                icon: 'warning',
                html: '<strong>La suma de los pesos debe ser exactamente 100%</strong>',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
        return true;
    }

    // Actualiza la suma en tiempo real
    document.querySelectorAll('.peso-input').forEach(function(input) {
        input.addEventListener('input', calcularSumaPesos);
    });

    // Ejecutar al cargar la página
    calcularSumaPesos();
    </script>
    <?php endif; ?>
</div>

<?php require_once "vistas/parte_inferior.php" ?>
