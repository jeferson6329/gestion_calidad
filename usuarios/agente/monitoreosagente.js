$(document).ready(function(){
    var tabla = $("#tablamonitoreos").DataTable({
        "order": [[0, "desc"]],
        "language": {
            "lengthMenu": "Mostrar _MENU_ monitoreos",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando monitoreos del _START_ al _END_ de un total de _TOTAL_ monitoreos",
            "infoEmpty": "Mostrando monitoreos del 0 al 0 de un total de 0 monitoreos",
            "infoFiltered": "(filtrado de un total de _MAX_ monitoreos)",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast":"Último",
                "sNext":"Siguiente",
                "sPrevious": "Anterior"
            },
            "sProcessing":"Procesando...",
        }
    });

    // Mostrar detalles y botones según estado
    $(document).on('click', '.btnVerMas', function(){
        var fila = $(this).closest('tr');
        var id = fila.data('id');

        // Llenar detalles
        $('#detalle_id_llamada').text(fila.data('id_llamada'));
        $('#detalle_nombre_asesor').text(fila.data('nombre_asesor'));
        $('#detalle_tipo_monitoreo').text(fila.data('tipo_monitoreo'));
        $('#detalle_canal').text(fila.data('canal'));
        $('#detalle_criterio1').text(fila.data('criterio1'));
        $('#detalle_criterio2').text(fila.data('criterio2'));
        $('#detalle_criterio3').text(fila.data('criterio3'));
        $('#detalle_criterio4').text(fila.data('criterio4'));
        $('#detalle_nota_final').text(fila.data('nota_final'));
        $('#detalle_fecha_llamada').text(fila.data('fecha_llamada'));
        $('#detalle_fecha_monitoreo').text(fila.data('fecha_monitoreo'));
        $('#detalle_estado').text(fila.data('estado'));
        $('#detalle_realizado_por').text(fila.data('realizado_por'));
        $('#detalle_descripcion').val(fila.data('descripcion'));
        $('#detalle_positivos').val(fila.data('positivos'));
        $('#detalle_mejorar').val(fila.data('mejorar'));
        $('#detalle_observacion_final').val(fila.data('comentario_refute'));
        $('#comentario_agente').val(fila.data('comentario_agente'));

        // Botones y contador según estado
        var estado = (fila.data('estado') || '').toLowerCase();
        var fechaRegistro = fila.data('fecha_registro');
        var mensajeDiv = $('#mensajeTiempoRestante');
        if(estado === "pendiente" && fechaRegistro) {
            $('#btnAprobar').show();
            $('#btnRefutar').show();
            mensajeDiv.show();
            iniciarContadorLaboral(fechaRegistro);
        } else {
            $('#btnAprobar').hide();
            $('#btnRefutar').hide();
            mensajeDiv.hide();
            if(window._intervalContadorLaboral) clearInterval(window._intervalContadorLaboral);
        }

        // Evento aprobar
        $('#btnAprobar').off('click').on('click', function(){
            var comentario = $('#comentario_agente').val().trim();
            if(comentario === "") {
                Swal.fire('Campo obligatorio', 'Debes ingresar un comentario antes de aprobar.', 'warning');
                $('#comentario_agente').focus();
                return;
            }
            $.post('aprobar_monitoreo.php', {id: id, comentario: comentario}, function(resp){
                Swal.fire('¡Aprobado!', 'El monitoreo ha sido aprobado.', 'success').then(() => {
                    $('#modalDetalle').modal('hide');
                    location.reload();
                });
            });
        });

        // Evento refutar
        $('#btnRefutar').off('click').on('click', function(){
            var comentario = $('#comentario_agente').val().trim();
            if(comentario === "") {
                Swal.fire('Campo obligatorio', 'Debes ingresar un comentario antes de refutar.', 'warning');
                $('#comentario_agente').focus();
                return;
            }
            $.post('refutar_monitoreo.php', {id: id, comentario: comentario}, function(resp){
                Swal.fire('¡Refutado!', 'Se ha enviado la notificación al supervisor para su revisión.', 'success').then(() => {
                    $('#modalDetalle').modal('hide');
                    location.reload();
                });
            });
        });

        // Cargar criterios e ítems
        $('#detalle_criterios').html('<div class="text-center">Cargando ítems...</div>');
        $.ajax({
            url: 'detalle_monitoreo.php',
            type: 'POST',
            data: { id_monitoreo: id },
            dataType: 'json',
            success: function(response) {
    let html = '';
    response.forEach(criterio => {
        html += `<h6>${criterio.nombre_criterio} <span style="font-weight:normal;">(Peso: ${criterio.peso * 100})</span></h6>`;
        html += `<table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Ítem</th>
                            <th>Cumple</th>
                        </tr>
                    </thead>
                    <tbody>`;
        criterio.items.forEach(item => {
            let cumpleBadge = item.cumple == 1
                ? '<span class="badge badge-success" style="color:#fff;">Sí</span>'
                : '<span class="badge badge-danger" style="color:#fff;">No</span>';
            html += `
                <tr>
                    <td>${item.nombre_item}</td>
                    <td>${cumpleBadge}</td>
                </tr>`;
        });
        html += '</tbody></table>';
    });
    document.getElementById('detalle_criterios').innerHTML = html;
}

        });

        $('#modalDetalle').modal('show');
    });

    // --------- CONTADOR Y FESTIVOS ---------
    function esFestivo(fecha) {
        const yyyy_mm_dd = fecha.toISOString().slice(0,10);
        return festivos.includes(yyyy_mm_dd);
    }

    function sumarHorasLaborales(fechaInicio, horasLaborales) {
        const HORA_INICIO = 7;
        const HORA_FIN = 18;
        let fecha = new Date(fechaInicio.replace(' ', 'T'));
        let horasSumadas = 0;
        while (horasSumadas < horasLaborales) {
            let dia = fecha.getDay();
            let hora = fecha.getHours();
            if (dia >= 1 && dia <= 5 && hora >= HORA_INICIO && hora < HORA_FIN && !esFestivo(fecha)) {
                horasSumadas++;
            }
            fecha.setHours(fecha.getHours() + 1);
        }
        return fecha;
    }

    function iniciarContadorLaboral(fechaRegistro) {
        const LIMITE_HORAS = 24;
        const contador = document.getElementById('contadorTiempoRestante');
        const fechaLimite = sumarHorasLaborales(fechaRegistro, LIMITE_HORAS);

        function actualizar() {
            let ahora = new Date();
            let msRestantes = fechaLimite - ahora;
            if (msRestantes <= 0) {
                contador.textContent = "00:00:00";
                clearInterval(window._intervalContadorLaboral);
                return;
            }
            let totalSegundos = Math.floor(msRestantes / 1000);
            let horas = Math.floor(totalSegundos / 3600);
            let minutos = Math.floor((totalSegundos % 3600) / 60);
            let segundos = totalSegundos % 60;
            contador.textContent =
                String(horas).padStart(2, '0') + ":" +
                String(minutos).padStart(2, '0') + ":" +
                String(segundos).padStart(2, '0');
        }
        if(window._intervalContadorLaboral) clearInterval(window._intervalContadorLaboral);
        actualizar();
        window._intervalContadorLaboral = setInterval(actualizar, 1000);
    }

    // --------- FILTROS AVANZADOS ---------
    function obtenerOpciones(colIdx) {
        var opciones = [];
        tabla.column(colIdx).data().unique().sort().each(function(d){
            if(d && opciones.indexOf(d) === -1) opciones.push(d);
        });
        return opciones;
    }

    $('#filtroCampo').on('change', function(){
        var colIdx = $(this).val();
        var html = '';
        if(colIdx === "tipo_monitoreo") {
            html = '<select id="filtroOpcion" class="form-control"><option value="">Todos</option>';
            tiposMonitoreo.forEach(function(op){
                html += '<option value="'+op.nombre+'">'+op.nombre+'</option>';
            });
            html += '</select>';
        } else if(colIdx === "canal") {
            html = '<select id="filtroOpcion" class="form-control"><option value="">Todos</option>';
            canales.forEach(function(op){
                html += '<option value="'+op.nombre+'">'+op.nombre+'</option>';
            });
            html += '</select>';
        } else if(colIdx === "fecha") {
            html = `
                <div class="container">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <input type="date" id="fechaDesde" class="form-control" placeholder="Desde" style="max-width: 200px;">
                        <input type="date" id="fechaHasta" class="form-control" placeholder="Hasta" style="max-width: 200px;">
                        <button id="btnFiltrarFecha" class="btn btn-primary">Filtrar</button>
                    </div>
                    <div id="errorFecha" class="text-danger mt-1"></div>
                </div>
            `;
        }
        $('#contenedorFiltroOpciones').html(html);

        // Limpia filtro anterior
        tabla.search('').columns().search('').draw();
    });

    // Aplica el filtro cuando se selecciona una opción
    $(document).on('change', '#filtroOpcion', function(){
        var campo = $('#filtroCampo').val();
        var valor = $(this).val();
        var colIdx = campo === "tipo_monitoreo" ? 3 : campo === "canal" ? 4 : null;
        if(colIdx !== null) {
            tabla.column(colIdx).search(valor).draw();
        }
    });

    // Filtro personalizado para rango de fechas en columna 11 (fecha monitoreo)
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var campo = $('#filtroCampo').val();
            if(campo !== "fecha") return true;

            var fechaInicio = $('#fechaDesde').val();
            var fechaFin = $('#fechaHasta').val();
            var fecha = data[11] || "";

            if(!fechaInicio && !fechaFin) return true;
            if(fechaInicio && fecha < fechaInicio) return false;
            if(fechaFin && fecha > fechaFin) return false;
            return true;
        }
    );

    // Detecta cambios en los inputs de fecha y redibuja la tabla, con validación
    $(document).on('click', '#btnFiltrarFecha', function(){
        var fechaInicio = $('#fechaDesde').val();
        var fechaFin = $('#fechaHasta').val();
        var errorDiv = $('#errorFecha');
        errorDiv.text('');
        if (!fechaInicio || !fechaFin) {
            errorDiv.text('Debe seleccionar ambas fechas.');
            return;
        }
        if (fechaInicio > fechaFin) {
            errorDiv.text('La fecha inicial debe ser igual o menor que la fecha final.');
            return;
        }
        tabla.draw();
    });
});
