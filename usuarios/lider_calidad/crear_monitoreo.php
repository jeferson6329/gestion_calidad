<?php require_once "vistas/parte_superior.php"; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container">
    <h1>Crear nuevo monitoreo</h1>

    <?php
    include_once 'bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    // Traer asesores (solo agentes)
    $consulta_asesores = "SELECT id, nombre, apellido FROM personas WHERE rol = 'agente'";
    $resultado_asesores = $conexion->prepare($consulta_asesores);
    $resultado_asesores->execute();
    $asesores = $resultado_asesores->fetchAll(PDO::FETCH_ASSOC);

    // Traer tipos de monitoreo
    $consulta_tipos = "SELECT id, nombre FROM tmonitoreo";
    $resultado_tipos = $conexion->prepare($consulta_tipos);
    $resultado_tipos->execute();
    $tipos_monitoreo = $resultado_tipos->fetchAll(PDO::FETCH_ASSOC);

    // Traer canales
    $consulta_canales = "SELECT id, nombre FROM canales";
    $resultado_canales = $conexion->prepare($consulta_canales);
    $resultado_canales->execute();
    $canales = $resultado_canales->fetchAll(PDO::FETCH_ASSOC);

    // Traer criterios con peso (solo para identificar IDs especiales)
    $consulta_criterios = "SELECT id, nombre FROM criterios ORDER BY id ASC";
    $resultado_criterios = $conexion->prepare($consulta_criterios);
    $resultado_criterios->execute();
    $criterios_bd = $resultado_criterios->fetchAll(PDO::FETCH_ASSOC);

    // Identificar los IDs de ECN, ECUF, ECC, ENC
    $id_ecn = $id_ecuf = $id_ecc = $id_enc = null;
    foreach($criterios_bd as $crit) {
        $nombre = strtoupper($crit['nombre']);
        if(strpos($nombre, 'ECN') !== false) $id_ecn = $crit['id'];
        if(strpos($nombre, 'ECUF') !== false) $id_ecuf = $crit['id'];
        if(strpos($nombre, 'ECC') !== false) $id_ecc = $crit['id'];
        if(strpos($nombre, 'ENC') !== false) $id_enc = $crit['id'];
    }
    ?>

    <form action="guardar_monitoreo.php" method="POST">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="asesor_id" class="col-form-label">Asesor:</label>
                <select class="form-control" id="asesor_id" name="asesor_id" required>
                    <option value="">Seleccione un asesor</option>
                    <?php foreach($asesores as $asesor): ?>
                        <option value="<?php echo $asesor['id']; ?>">
                            <?php echo $asesor['nombre'].' '.$asesor['apellido']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md=6">
                <label for="id_llamada" class="col-form-label">ID Llamada:</label>
                <input type="text" class="form-control" id="id_llamada" name="id_llamada" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="tipo_monitoreo">Tipo de monitoreo:</label>
                <select class="form-control" id="tipo_monitoreo" name="tipo_monitoreo" required>
                    <option value="">Seleccione un tipo de monitoreo</option>
                    <?php foreach($tipos_monitoreo as $tipo): ?>
                        <option value="<?php echo $tipo['id']; ?>">
                            <?php echo $tipo['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="canal">Canal:</label>
                <select class="form-control" id="canal" name="canal" required>
                    <option value="">Seleccione un canal</option>
                    <?php foreach($canales as $canal): ?>
                        <option value="<?php echo $canal['id']; ?>">
                            <?php echo $canal['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Aquí se mostrarán los niveles de tipificación del canal seleccionado -->
        <div id="niveles_tipificacion_contenedor" class="mb-3"></div>

        <!-- Aquí se mostrarán los criterios e ítems -->
        <div id="contenedor_criterios" class="mt-4"></div>

        <!-- Resumen de criterios -->
        <div id="resumen_criterios" class="row"></div>

        <!-- Inputs para ECN, ECUF, ECC, ENC -->
        <div class="row" style="display: none;">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="ecn">ECN:</label>
                    <input type="number" class="form-control" id="ecn" name="ecn" value="0" readonly step="any">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group" >
                    <label for="ecuf">ECUF:</label>
                    <input type="number" class="form-control" id="ecuf" name="ecuf" value="0" readonly step="any">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="ecc">ECC:</label>
                    <input type="number" class="form-control" id="ecc" name="ecc" value="0" readonly step="any">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="enc">ENC:</label>
                    <input type="number" class="form-control" id="enc" name="enc" value="0" readonly step="any">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="valor_total">Nota final:</label>
            <input type="number" class="form-control" id="valor_total" name="valor_total" readonly step="any">
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="fecha_llamada">Fecha de llamada:</label>
                <input type="date" class="form-control" id="fecha_llamada" name="fecha_llamada" required>
            </div>
            <div class="form-group col-md-6">
                <label for="fecha_monitoreo">Fecha de monitoreo:</label>
                <input type="date" class="form-control" id="fecha_monitoreo" name="fecha_monitoreo" required>
            </div>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción de la llamada:</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="aspectos_positivos">Aspectos positivos:</label>
            <textarea class="form-control" id="aspectos_positivos" name="aspectos_positivos" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label for="aspectos_mejorar">Aspectos a mejorar:</label>
            <textarea class="form-control" id="aspectos_mejorar" name="aspectos_mejorar" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label for="realizado_por">Realizado por:</label>
            <input type="text" class="form-control" id="realizado_por" name="realizado_por"
                   value="<?php echo $_SESSION['s_nombre'] . ' ' . $_SESSION['s_apellido']; ?>" readonly>
        </div>
        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['s_id']; ?>">
        <button type="submit" class="btn btn-primary">Guardar monitoreo</button>
    </form>
</div>

<script>
$(document).ready(function(){
    // Guardar pesos y nombres de criterios por id
    let pesosCriterios = {};
    let nombresCriterios = {};

    function generarResumenCriterios(criterios) {
        let html = '';
        criterios.forEach(function(criterio){
            pesosCriterios[criterio.id] = parseFloat(criterio.peso);
            nombresCriterios[criterio.id] = criterio.nombre;
            html += `
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="criterio_${criterio.id}">${criterio.nombre}:</label>
                        <input type="number" class="form-control criterio-valor"
                               id="criterio_${criterio.id}"
                               name="criterios[${criterio.id}]"
                               value="0" readonly>
                        <small class="form-text text-muted">Peso: ${(criterio.peso*100).toFixed(0)}%</small>
                    </div>
                </div>
            `;
        });
        $('#resumen_criterios').html(html);
    }

    function recalcularTotal() {
        let criteriosValores = {}; // { criterio_id: { cumplido: X, total: Y, algunNo: true/false } }

        $('#contenedor_criterios select[name^="cumple"]').each(function(){
            let cumple = $(this).val();
            let valor = parseFloat($(this).data('valor')) || 0;
            let criterio_id = $(this).data('criterio');

            if (!criterio_id) return;

            if (!criteriosValores[criterio_id]) {
                criteriosValores[criterio_id] = { cumplido: 0, total: 0, algunNo: false };
            }

            criteriosValores[criterio_id].total += valor;

            if (cumple === "1") {
                criteriosValores[criterio_id].cumplido += valor;
            } else if (cumple === "0") {
                criteriosValores[criterio_id].algunNo = true; // Marcamos que tiene un "No"
            }
        });

        let total = 0;

        for (let id in criteriosValores) {
            let datos = criteriosValores[id];
            let cumplimiento = 0;

            // Regla especial: si el criterio es el 3 y tiene al menos un "No", se va a 0
            if (parseInt(id) === 2,3 && datos.algunNo) {
                cumplimiento = 0;
            } else {
                cumplimiento = datos.total > 0 ? datos.cumplido / datos.total : 0;
            }


            let ponderado = cumplimiento * (pesosCriterios[id] || 0);
            let valorFinal = ponderado * 100;

            // Actualizar input individual del criterio
            $('#criterio_' + id).val((cumplimiento * 100).toFixed(2));

            total += valorFinal;
        }

        // Actualiza los inputs ECN, ECUF, ECC, ENC si existen
        <?php if($id_ecn): ?> $('#ecn').val($('#criterio_<?php echo $id_ecn; ?>').val()); <?php endif; ?>
        <?php if($id_ecuf): ?> $('#ecuf').val($('#criterio_<?php echo $id_ecuf; ?>').val()); <?php endif; ?>
        <?php if($id_ecc): ?> $('#ecc').val($('#criterio_<?php echo $id_ecc; ?>').val()); <?php endif; ?>
        <?php if($id_enc): ?> $('#enc').val($('#criterio_<?php echo $id_enc; ?>').val()); <?php endif; ?>

        // Actualiza la nota final
        $('#valor_total').val(total.toFixed(2));
    }


    // Cuando cambia el canal
    $('#canal').change(function(){
        var canal_id = $(this).val();

        // Limpiar niveles anteriores
        $('#nivel_tipificacion').remove();
        $('#nivel_tipificacion2').remove();

        $('#niveles_tipificacion_contenedor').empty();

        if(canal_id){
            // Obtener nivel 1
            $.ajax({
                url: 'obtener_niveles_tipificacion.php',
                type: 'POST',
                dataType: 'json',
                data: { canal_id: canal_id },
                success: function(niveles){
                    // Limpiar selects anteriores
                    $('#nivel_tipificacion').remove();
                    $('#nivel_tipificacion2').remove();

                    if(niveles.length > 0){
                        let html = '<label for="nivel_tipificacion"><strong>Nivel de tipificación para este canal:</strong></label>';
                        html += '<select class="form-control mb-3" id="nivel_tipificacion" name="nivel_tipificacion" required>';
                        html += '<option value="">Seleccione un nivel de tipificación</option>';
                        niveles.forEach(function(n){
                            html += '<option value="'+n.id+'">'+n.texto+'</option>';
                        });
                        html += '</select>';
                        $('#niveles_tipificacion_contenedor').append(html);
                    } else {
                        $('#niveles_tipificacion_contenedor').append('<div class="alert alert-info">No hay niveles de tipificación para este canal.</div>');
                    }
                }
            });
        }
    });

    // Delegado: cuando cambia el primer nivel, traer subnivel
    $('#niveles_tipificacion_contenedor').on('change', '#nivel_tipificacion', function(){
        var nivel1_id = $(this).val();
        var canal_id = $('#canal').val();

        // Eliminar nivel2 viejo si existe
        $('#nivel_tipificacion2').remove();

        if(nivel1_id && canal_id){
            $.ajax({
                url: 'obtener_niveles_tipificacion2.php',
                type: 'POST',
                dataType: 'json',
                data: { nivel1_id: nivel1_id, canal_id: canal_id },
                success: function(niveles2){
                    if(niveles2.length > 0){
                        let html = '<label for="nivel_tipificacion2"><strong>Subnivel de tipificación:</strong></label>';
                        html += '<select class="form-control mb-3" id="nivel_tipificacion2" name="nivel_tipificacion2" required>';
                        html += '<option value="">Seleccione un subnivel</option>';
                        niveles2.forEach(function(n){
                            html += '<option value="'+n.id+'">'+n.texto+'</option>';
                        });
                        html += '</select>';
                        $('#niveles_tipificacion_contenedor').append(html);
                    } else {
                        // No hay subnivel, al menos asegurarse que name nivel_tipificacion2 no exista
                        $('#nivel_tipificacion2').remove();
                    }
                }
            });
        } else {
            $('#nivel_tipificacion2').remove();
        }
    });

    // Cuando cambia algún select de criterios de cumplimiento
    $('#contenedor_criterios').on('change', '.cumple-select', recalcularTotal);

    // Cuando cambia canal y se limpian los niveles, también limpiar criterios
    $('#canal').change(function(){
        $('#contenedor_criterios').html('');
        $('#resumen_criterios').html('');
        pesosCriterios = {};
        nombresCriterios = {};
    });

    // Cargar criterios e ítems cuando ya haya canal seleccionado (y después de que cambie canal)
    $('#canal').change(function(){
        var canal_id = $(this).val();
        if(canal_id){
            $.ajax({
                url: 'obtener_criterios.php',
                type: 'POST',
                dataType: 'json',
                data: { canal_id: canal_id },
                success: function(criterios){
                    console.log(criterios);
                    generarResumenCriterios(criterios);

                    let html = '';
                    criterios.forEach(function(criterio){
                        html += '<div class="card mb-2">';
                        html += '<div class="card-header font-weight-bold">'+criterio.nombre+'</div>';
                        html += '<ul class="list-group list-group-flush">';
                        criterio.items.forEach(function(item){
                            html += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                            html += '<span>'+item.nombre+' <span class="badge badge-info ml-2"></span></span>';
                            html += '<select class="form-control ml-2 cumple-select" style="width:120px;display:inline-block" name="cumple['+item.id+']" data-valor="'+(item.valor !== null ? item.valor : 0)+'" data-criterio="'+criterio.id+'" required>';
                            
                            html += '<option value="1">Sí</option>';
                            html += '<option value="0">No</option>';
                            html += '</select>';
                            html += '</li>';
                        });
                        html += '</ul>';
                        html += '<div class="card-footer text-muted">Peso: '+(criterio.peso*100)+'%</div>';
                        html += '</div>';
                    });
                    $('#contenedor_criterios').html(html);

                    // Adjuntar evento
                    $('.cumple-select').change(recalcularTotal);

                    // Inicializar valor
                    recalcularTotal();
                }
            });
        }
    });

    // Validar fechas y submit
    $('form').on('submit', function(e) {
        e.preventDefault();

        // Validar que la fecha de llamada no sea después de la fecha de monitoreo
        const fechaLlamada = new Date($('#fecha_llamada').val());
        const fechaMonitoreo = new Date($('#fecha_monitoreo').val());

        if (fechaLlamada > fechaMonitoreo) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha inválida',
                text: 'La fecha de la llamada no puede ser posterior a la fecha del monitoreo.',
                confirmButtonText: 'Aceptar'
            });
            $('#fecha_llamada').focus();
            return;
        }

        // Verificar que el nivel 1 esté seleccionado
        if ($('#nivel_tipificacion').length && $('#nivel_tipificacion').val() === "") {
            Swal.fire({
                icon: 'warning',
                title: 'Nivel de tipificación faltante',
                text: 'Seleccione un nivel de tipificación.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // Verificar que si existe nivel 2 en el formulario, que esté seleccionado
        if ($('#nivel_tipificacion2').length && $('#nivel_tipificacion2').val() === "") {
            Swal.fire({
                icon: 'warning',
                title: 'Subnivel de tipificación faltante',
                text: 'Seleccione un subnivel de tipificación.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        recalcularTotal();

        setTimeout(() => {
            this.submit();
        }, 10);
    });

});
</script>

<?php require_once "vistas/parte_inferior.php"; ?>
