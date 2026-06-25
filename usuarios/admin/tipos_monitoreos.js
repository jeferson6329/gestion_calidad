$(document).ready(function(){
    let id = null;
    let opcion = 1;
    let fila; // capturar la fila para editar o borrar

    let tablatmonitoreo = $("#tablatmonitoreo").DataTable({
        "columnDefs": [{
            "targets": -1,
            "data": null,
            "defaultContent": "<div class='text-center'><div class='btn-group'><button class='btn btn-primary btnEditarMonitoreo'>Editar</button><button class='btn btn-danger btnBorrarMonitoreo'>Borrar</button></div></div>"
        }],
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "sProcessing": "Procesando...",
        }
    });

    // Botón NUEVO
    $("#btnNuevo").click(function(){
        $("#formtmonitoreo").trigger("reset");
        $(".modal-header").css("background-color", "#1cc88a");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Nuevo tipo de monitoreo");
        $("#modalCRUD").modal("show");
        id = null;
        opcion = 1; // alta
    });

    // Botón EDITAR
    $(document).on("click", ".btnEditarMonitoreo", function(){
        fila = $(this).closest("tr");
        id = parseInt(fila.find('td:eq(0)').text());
        nombre = fila.find('td:eq(1)').text();

        $("#nombre").val(nombre);

        opcion = 2; // editar

        $(".modal-header").css("background-color", "#4e73df");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Editar tipo de monitoreo");
        $("#modalCRUD").modal("show");
    });

    // Botón BORRAR
    $(document).on("click", ".btnBorrarMonitoreo", function(e){
        e.preventDefault();
        var fila = $(this).closest("tr");
        var id = parseInt(fila.find('td:eq(0)').text());
        var opcion = 3; // borrar

        Swal.fire({
            title: '¿Está seguro?',
            text: "¡Esta acción no se puede deshacer!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, borrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "bd/crud_tipo_monitoreo.php",
                    type: "POST",
                    dataType: "json",
                    data: {opcion: opcion, id: id},
                    success: function(response){
                        tablatmonitoreo.row(fila[0]).remove().draw();
                        Swal.fire('¡Eliminado!', 'El registro ha sido eliminado.', 'success');
                    },
                    error: function(xhr, status, error){
                        Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                    }
                });
            }
        });
    });

    // Guardar (crear o editar)
    $("#formtmonitoreo").submit(function(e){
        e.preventDefault();
        nombre = $.trim($("#nombre").val());

        $.ajax({
            url: "bd/crud_tipo_monitoreo.php",
            type: "POST",
            dataType: "json",
            data: {nombre: nombre, id: id, opcion: opcion},
            success: function(data){
                let botones = "<div class='text-center'><div class='btn-group'><button class='btn btn-primary btnEditarMonitoreo'>Editar</button><button class='btn btn-danger btnBorrarMonitoreo'>Borrar</button></div></div>";
                if(opcion == 1){
                    tablatmonitoreo.row.add([data.id, data.nombre, botones]).draw();
                } else {
                    tablatmonitoreo.row(fila).data([data.id, data.nombre, botones]).draw();
                }
            }
        });
        $("#modalCRUD").modal("hide");
    });
});