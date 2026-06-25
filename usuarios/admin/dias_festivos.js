$(document).ready(function(){
    let id = null;
    let opcion = 1;
    let fila;

    let tablafestivos = $("#tablafestivos").DataTable({
        "columnDefs": [{
            "targets": -1,
            "data": null,
            "defaultContent": "<div class='text-center'><div class='btn-group'><button class='btn btn-primary btnEditarFestivo'>Editar</button><button class='btn btn-danger btnBorrarFestivo'>Borrar</button></div></div>"
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
        $("#formfestivos").trigger("reset");
        $(".modal-header").css("background-color", "#1cc88a");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Nuevo ");
        $("#modalCRUD").modal("show");
        id = null;
        opcion = 1;
        // El valor de registrado_por ya está puesto por PHP, no lo cambies aquí
    });

    // Botón EDITAR
    $(document).on("click", ".btnEditarFestivo", function(){
        fila = $(this).closest("tr");
        id = parseInt(fila.find('td:eq(0)').text());
        fecha = fila.find('td:eq(1)').text();
        descripcion = fila.find('td:eq(2)').text();

        $("#fecha").val(fecha);
        $("#descripcion").val(descripcion);
        // No modificar el campo registrado_por, que queda con el usuario de sesión

        opcion = 2;

        $(".modal-header").css("background-color", "#4e73df");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Editar ");
        $("#modalCRUD").modal("show");
    });

    $(document).on("click", ".btnBorrarFestivo", function(){
    fila = $(this).closest("tr");
    id = parseInt(fila.find('td:eq(0)').text());
    opcion = 3;

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
                url: "bd/crud_festivos.php",
                type: "POST",
                dataType: "json",
                data: {opcion: opcion, id: id},
                success: function(response){
                    tablafestivos.row(fila[0]).remove().draw();
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
    $("#formfestivos").submit(function(e){
        e.preventDefault();
        fecha = $.trim($("#fecha").val());
        descripcion = $.trim($("#descripcion").val());
        registrado_por = $.trim($("#registrado_por").val());

        $.ajax({
            url: "bd/crud_festivos.php",
            type: "POST",
            dataType: "json",
            data: {fecha: fecha, descripcion: descripcion, registrado_por: registrado_por, id: id, opcion: opcion},
            success: function(data){
                let botones = "<div class='text-center'><div class='btn-group'><button class='btn btn-primary btnEditarFestivo'>Editar</button><button class='btn btn-danger btnBorrarFestivo'>Borrar</button></div></div>";
                if(opcion == 1){
                    tablafestivos.row.add([data.id, data.fecha, data.descripcion, data.registrado_por, botones]).draw();
                } else {
                    tablafestivos.row(fila).data([data.id, data.fecha, data.descripcion, data.registrado_por, botones]).draw();
                }
            }
        });
        $("#modalCRUD").modal("hide");
    });
});
