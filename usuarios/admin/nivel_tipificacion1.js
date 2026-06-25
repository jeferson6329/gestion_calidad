$(document).ready(function () {
    let id = null;
    let opcion = null;
    let fila;

    let tabla = $("#tabla_nivel_tipificacion1").DataTable({
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
            infoFiltered: "(filtrado de un total de _MAX_ registros)",
            sSearch: "Buscar:",
            oPaginate: {
                sFirst: "Primero",
                sLast: "Último",
                sNext: "Siguiente",
                sPrevious: "Anterior"
            },
            sProcessing: "Procesando...",
        }
    });

    // NUEVO
    $("#btnNuevo").click(function () {
        $("#formNivelTipificacion1").trigger("reset");
        $(".modal-header").css("background-color", "#1cc88a");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Nuevo Registro");
        $("#modalCRUD").modal("show");
        id = null;
        opcion = 1;
    });

    // EDITAR
    $(document).on("click", ".btnEditar", function () {
        fila = $(this).closest("tr");
        id = parseInt(fila.find('td:eq(0)').text());
        const texto = fila.find('td:eq(1)').text();

        $("#id").val(id);
        $("#texto").val(texto);

        opcion = 2;

        $(".modal-header").css("background-color", "#4e73df");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Editar Registro");
        $("#modalCRUD").modal("show");
    });

    // BORRAR
    $(document).on("click", ".btnBorrar", function () {
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
                    url: "bd/crud_nivel_tipificacion1.php",
                    type: "POST",
                    dataType: "json",
                    data: { opcion: opcion, id: id },
                    success: function () {
                        tabla.row(fila).remove().draw();
                        Swal.fire('¡Eliminado!', 'El registro ha sido eliminado.', 'success');
                    },
                    error: function () {
                        Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                    }
                });
            }
        });
    });

    // GUARDAR
    $("#formNivelTipificacion1").submit(function (e) {
        e.preventDefault();

        const texto = $.trim($("#texto").val());
        const registrado_por = $.trim($("#registrado_por").val());

        $.ajax({
            url: "bd/crud_nivel_tipificacion1.php",
            type: "POST",
            dataType: "json",
            data: { id: id, texto: texto, registrado_por: registrado_por, opcion: opcion },
            success: function (data) {
                if (opcion === 1) {
                    tabla.row.add([
                        data.id,
                        data.texto,
                        data.registrado_por,
                        data.fecha_creacion,
                        `
                        <div class='text-center'>
                            <div class='btn-group'>
                                <button class='btn btn-info btnEditar'>Editar</button>
                                <button class='btn btn-danger btnBorrar'>Borrar</button>
                            </div>
                        </div>`
                    ]).draw();
                } else {
                    tabla.row(fila).data([
                        data.id,
                        data.texto,
                        data.registrado_por,
                        data.fecha_creacion,
                        `
                        <div class='text-center'>
                            <div class='btn-group'>
                                <button class='btn btn-info btnEditar'>Editar</button>
                                <button class='btn btn-danger btnBorrar'>Borrar</button>
                            </div>
                        </div>`
                    ]).draw();
                }

                $("#modalCRUD").modal("hide");
            },
            error: function () {
                Swal.fire('Error', 'No se pudo guardar el registro.', 'error');
            }
        });
    });
});
