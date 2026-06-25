$(document).ready(function(){
    var opcion = 1; // 1=crear, 2=editar, 3=borrar

    tablaPersonas = $("#tablaPersonas").DataTable({
        "columnDefs":[{
            "targets": -1,
            "data":null,
            "defaultContent": "<div class='text-center'><div class='btn-group'><button class='btn btn-primary btnEditar'>Editar</button><button class='btn btn-danger btnBorrar'>Borrar</button></div></div>"  
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
                "sLast":"Último",
                "sNext":"Siguiente",
                "sPrevious": "Anterior"
            },
            "sProcessing":"Procesando...",
        }
    });

    $("#btnNuevo").click(function(){
        opcion = 1;
        $("#formPersonas").trigger("reset");
        $("#restablecerClaveGroup").hide();
        $("#rol").val("agente");
        $(".modal-title").text("Nuevo usuario");
        $("#modalCRUD").modal("show");
    });    
    
    //botón EDITAR    
    $(document).on("click", ".btnEditar", function(){
        opcion = 2;
        var fila = $(this).closest("tr");
        $("#id").val(fila.data("id"));
        $("#documento").val(fila.data("documento"));
        $("#nombre").val(fila.data("nombre"));
        $("#apellido").val(fila.data("apellido"));
        $("#correo").val(fila.data("correo"));
        $("#rol").val(fila.data("rol"));

        // Selecciona el supervisor en el select
        let supervisor_id = fila.data("supervisor_id");
        $("#supervisor_id").val(supervisor_id);
        

        $("#restablecerClaveGroup").show();
        $("#restablecerClave").prop('checked', false);

        $(".modal-title").text("Editar usuario");
        $("#modalCRUD").modal("show");
    });

    //botón BORRAR
    $(document).on("click", ".btnBorrar", function(){    
        opcion = 3;
        var fila = $(this).closest("tr");
        var id = fila.data("id");
        var respuesta = confirm("¿Está seguro de eliminar al usuario?");
        if(respuesta){
            $.ajax({
                url: "bd/crud.php",
                type: "POST",
                dataType: "json",
                data: {opcion:opcion, id:id},
                success: function(){
                    location.reload();
                }
            });
        }   
    });
        
    $("#formPersonas").submit(function(e){
        e.preventDefault();

        let id = $("#id").val();
        let documento = $.trim($("#documento").val());
        let correo = $.trim($("#correo").val());

        // Validar documento y correo antes de guardar
        $.post('bd/validar_datos_duplicados.php', {documento: documento, correo: correo, id: id}, function(res){
            let data = JSON.parse(res);

            // Validación: al editar, permite el documento si es el mismo registro
            if(data.documento && (opcion === 1 || (opcion === 2 && documento !== $("#documento").data("original")))){
                Swal.fire('Error', 'El documento ya existe.', 'error');
                return;
            }
            if(data.correo){
                Swal.fire('Error', 'El correo ya existe.', 'error');
                return;
            }

            // Guardar usuario
            let nombre = $.trim($("#nombre").val());
            let apellido = $.trim($("#apellido").val());
            let rol = $.trim($("#rol").val());
            let supervisor_id = $.trim($("#supervisor_id").val());
            let supervisor = $.trim($("#supervisor_id option:selected").text());
            let resetPass = $("#restablecerClave").is(":checked") ? 1 : 0;
            let requiereCambioClave = $("#restablecerClave").is(":checked") ? 1 : 0;

            $.ajax({
                url: "bd/crud.php",
                type: "POST",
                dataType: "json",
                data: {
                    id: id,
                    documento: documento,
                    nombre: nombre,
                    apellido: apellido,
                    correo: correo,
                    rol: rol,
                    supervisor_id: supervisor_id,
                    supervisor: supervisor,
                    resetPass: resetPass,
                    requiere_cambio_clave: requiereCambioClave,
                    opcion: opcion
                },
                success: function(data){  
                    $("#modalCRUD").modal("hide");
                    if(opcion === 1){
                        Swal.fire('¡Usuario creado!', 'Usuario creado exitosamente.', 'success');
                    } else if(opcion === 2){
                        Swal.fire('¡Usuario editado!', 'Usuario editado correctamente.', 'success');
                    }
                    setTimeout(function(){ location.reload(); }, 1500);
                }        
            });
        });
    });    

    // Filtro por rol
    $(".filtro-rol").click(function(){
        let rol = $(this).data("rol");
        $("#tablaPersonas tbody tr").each(function(){
            if(rol === "" || $(this).data("rol") === rol){
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});