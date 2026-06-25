$(document).ready(function () {
    let id, opcion, fila;

    // Inicializar DataTable con buscador y paginación
    let tabla = $("#tablaNivelesTipificacion").DataTable({
        responsive: true,
        autoWidth: false,
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

    // === CREAR NUEVO ===
    $("#btnNuevo").click(function () {
        $("#formNivelesTipificacion").trigger("reset");
        opcion = 1; // crear
        id = null;

        $(".modal-header").css("background-color", "#1cc88a");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Nuevo Nivel de Tipificación");

        $("#modalCRUD").modal("show");
    });

    // === SUBMIT (Crear / Editar) ===
    $("#formNivelesTipificacion").submit(function (e) {
        e.preventDefault();

        const texto = $.trim($("#texto").val());
        const canal_id = $.trim($("#canal_id").val());
        const nivel_padre_id = $.trim($("#nivel_padre_id").val());
        const registrado_por = $.trim($("#registrado_por").val());

        $.ajax({
            url: "bd/crud_niveles_tipificacion.php",
            type: "POST",
            dataType: "json",
            data: {
                id: id,
                texto: texto,
                canal_id: canal_id,
                nivel_padre_id: nivel_padre_id,
                registrado_por: registrado_por,
                opcion: opcion,
            },
            success: function (data) {
                const botones = `
                    <div class='text-center'>
                        <div class='btn-group'>
                            <button class='btn btn-info btnEditarCanal'
                                data-id='${data.id}'
                                data-texto='${data.texto}'
                                data-canal_id='${data.canal_id}'
                                data-nivel_padre_id='${data.nivel_padre_id}'
                            >Editar</button>
                            <button class='btn btn-danger btnBorrarCanal' data-id='${data.id}'>Borrar</button>
                        </div>
                    </div>`;

                if (opcion === 1) {
                    // Crear
                    tabla.row.add([
                        data.id,
                        data.texto,
                        data.canal_nombre ? data.canal_nombre : "Sin canal",
                        data.nivel_padre_texto ? data.nivel_padre_texto : "Sin nivel padre",
                        data.registrado_por,
                        botones,
                    ]).draw(false);
                } else {
                    // Editar
                    tabla.row(fila).data([
                        data.id,
                        data.texto,
                        data.canal_nombre ? data.canal_nombre : "Sin canal",
                        data.nivel_padre_texto ? data.nivel_padre_texto : "Sin nivel padre",
                        data.registrado_por,
                        botones,
                    ]).draw(false);
                }

                $("#modalCRUD").modal("hide");
            },
            error: function () {
                Swal.fire("Error", "No se pudo guardar el registro.", "error");
            },
        });
    });

    // === EDITAR ===
    $(document).on("click", ".btnEditarCanal", function () {
        fila = $(this).closest("tr");

        id = parseInt($(this).data("id"));
        const texto = $(this).data("texto");
        const canal_id = $(this).data("canal_id");
        const nivel_padre_id = $(this).data("nivel_padre_id");

        $("#id").val(id);
        $("#texto").val(texto);
        $("#canal_id").val(canal_id);
        $("#nivel_padre_id").val(nivel_padre_id);

        opcion = 2; // editar

        $(".modal-header").css("background-color", "#4e73df");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Editar Nivel de Tipificación");

        $("#modalCRUD").modal("show");
    });

    // === BORRAR ===
    $(document).on("click", ".btnBorrarCanal", function () {
        id = $(this).data("id");
        fila = $(this).closest("tr");

        Swal.fire({
            title: "¿Seguro de eliminar?",
            text: "Este registro no se podrá recuperar",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "bd/crud_niveles_tipificacion.php",
                    type: "POST",
                    dataType: "json",
                    data: { opcion: 3, id: id },
                    success: function (res) {
                        if (res.success) {
                            tabla.row(fila).remove().draw();
                            Swal.fire("Eliminado", "El registro ha sido borrado.", "success");
                        } else {
                            Swal.fire("Error", "No se pudo eliminar el registro.", "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error", "No se pudo conectar al servidor.", "error");
                    },
                });
            }
        });
    });
});
