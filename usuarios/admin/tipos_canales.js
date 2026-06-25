$(document).ready(function(){
    let id = null;
    let opcion = 1; // 1: Crear, 2: Editar, 3: Eliminar
    let fila; // fila seleccionada

    let tablacanales = $("#tablacanales").DataTable({
        "columnDefs": [{
            "targets": -1,
            "data": null,
            "defaultContent": `
                <div class='text-center'>
                    <div class='btn-group'>
                        <button class='btn btn-info btn-sm btnEditarCanal'>Editar</button>
                        <button class='btn btn-danger btn-sm btnCanalesEliminar'>Eliminar</button>

                    </div>
                </div>`
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

    // Botón Nuevo canal
    $("#btnNuevo").click(function(){
        $("#formcanales").trigger("reset");
        $(".modal-header").css("background-color", "#1cc88a");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Nuevo canal");
        $("#modalCRUD").modal("show");
        id = null;
        opcion = 1;
    });

// Botón Editar
    $(document).on("click", ".btnEditarCanal", function(){
        fila = $(this).closest("tr");
        id = parseInt(fila.find('td:eq(0)').text());
        let nombre = fila.find('td:eq(1)').text();

        $("#nombre").val(nombre);

        opcion = 2; // editar
        $(".modal-header").css("background-color", "#4e73df");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Editar canal");
        $("#modalCRUD").modal("show");
    });

    // Botón Eliminar
    $(document).on("click", ".btnCanalesEliminar", function(){

        fila = $(this).closest("tr");
        id = parseInt(fila.find('td:eq(0)').text());
        opcion = 3; // eliminar

        Swal.fire({
            title: '¿Está seguro?',
            text: "¡Esta acción no se puede deshacer!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: "bd/crud_canales.php",
                    type: "POST",
                    dataType: "json",
                    data: {opcion: opcion, id: id},
                    success: function(response){
                        if(response.success){
                            tablacanales.row(fila).remove().draw();
                            Swal.fire('¡Eliminado!', 'El registro ha sido eliminado.', 'success');
                        } else {
                            Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                        }
                    },
                    error: function(xhr, status, error){
                        console.error("Error AJAX:", xhr.responseText);
                        Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                    }
                });
            }
        });
    });

    // Guardar (Crear o Editar)
    $("#formcanales").submit(function(e){
        e.preventDefault();
        let nombre = $.trim($("#nombre").val());

        if(nombre.length === 0){
            Swal.fire('Error', 'El campo nombre no puede estar vacío.', 'warning');
            return;
        }

        $.ajax({
            url: "bd/crud_canales.php",
            type: "POST",
            dataType: "json",
            data: {nombre: nombre, id: id, opcion: opcion},
            success: function(data){
                let botones = `
                    <div class='text-center'>
                        <div class='btn-group'>
                            <button class='btn btn-info btn-sm btnEditarCanal'>Editar</button>
                            <button class='btn btn-danger btn-sm btnEliminarCanal'>Eliminar</button>
                        </div>
                    </div>`;
                if(opcion == 1){ // nuevo registro
                    tablacanales.row.add([data.id, data.nombre, botones]).draw();
                } else { // editar registro
                    tablacanales.row(fila).data([data.id, data.nombre, botones]).draw();
                }
                $("#modalCRUD").modal("hide");
            },
            error: function(xhr, status, error){
                console.error("Error AJAX:", xhr.responseText);
                Swal.fire('Error', 'No se pudo guardar el registro.', 'error');
            }
        });
    });
});