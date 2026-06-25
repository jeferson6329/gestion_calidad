$(document).ready(function(){
    let id = null;

    // Inicializa DataTable solo con botón Editar
    $(document).ready(function() {
    $('#tablamonitoreos').DataTable({
        "order": [[0, "desc"]], // Ordenar por ID DESC
        "columnDefs": [
            {
                "targets": -1,
                "data": null,
                "defaultContent": "<div class='text-center'><div class='btn-group'><button class='btn btn-warning btneditarmonitoreo'>Reevaluar</button></div></div>"
            },
            {
                "targets": 0, // Columna ID
                "visible": true, // La ocultas
                "searchable": true
            }
        ],
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
});


    // Botón EDITAR (Reevaluar)
    $(document).on("click", ".btneditarmonitoreo", function(){
        let fila = $(this).closest("tr");
        let id = fila.find('td:eq(0)').text();
        window.location.href = "reevaluar_monitoreo.php?id=" + id;
    });

    // Filtros avanzados
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
        if(colIdx === "1" || colIdx === "2" || colIdx === "3") {
            var opciones = obtenerOpciones(colIdx);
            html = '<select id="filtroOpcion" class="form-control"><option value="">Todos</option>';
            opciones.forEach(function(op){
                html += '<option value="'+op+'">'+op+'</option>';
            });
            html += '</select>';
        } else if(colIdx === "10") {
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
                </div>
            `;
        }
        $('#contenedorFiltroOpciones').html(html);

        // Limpia filtro anterior
        tabla.search('').columns().search('').draw();
    });

    // Aplica el filtro cuando se selecciona una opción
    $(document).on('change keyup', '#filtroOpcion', function(){
        var colIdx = $('#filtroCampo').val();
        var valor = $(this).val();
        if(colIdx) {
            tabla.column(colIdx).search(valor).draw();
        }
    });

    // Filtro personalizado para rango de fechas en columna 10 (fecha monitoreo)
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var colIdx = $('#filtroCampo').val();
            if(colIdx !== "10") return true; // Solo aplica si es filtro de fecha monitoreo

            var fechaInicio = $('#filtroFechaInicio').val();
            var fechaFin = $('#filtroFechaFin').val();
            var fecha = data[10] || ""; // Columna de fecha monitoreo

            if(!fechaInicio && !fechaFin) return true;
            if(fechaInicio && fecha < fechaInicio) return false;
            if(fechaFin && fecha > fechaFin) return false;
            return true;
        }
    );

    // Detecta cambios en los inputs de fecha y redibuja la tabla, con validación
    $(document).on('change', '#filtroFechaInicio, #filtroFechaFin', function(){
        var fechaInicio = $('#filtroFechaInicio').val();
        var fechaFin = $('#filtroFechaFin').val();
        if(fechaInicio && fechaFin && fechaFin < fechaInicio){
            Swal.fire({
                icon: 'warning',
                title: 'Rango de fechas inválido',
                text: 'La fecha "Hasta" no puede ser menor que la fecha "Desde".',
                confirmButtonText: 'Aceptar'
            });
            $('#filtroFechaFin').val('');
            return;
        }
        $('#tablamonitoreos').DataTable().draw();
    });
    
});