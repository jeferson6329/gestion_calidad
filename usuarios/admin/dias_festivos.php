<?php require_once "vistas/parte_superior.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- INICIO del contenido principal -->
<div class="container">
    <h1>Días festivos</h1>

    <?php
    include_once '../admin/bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    $consulta = "SELECT id, fecha, descripcion, registrado_por FROM dias_festivos";
    $resultado = $conexion->prepare($consulta);
    $resultado->execute();
    $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="row">
        <div class="col-lg-12">            
            <button id="btnNuevo" type="button" class="btn btn-success" data-toggle="modal">Nuevo</button>    
        </div>    
    </div>    

    <br>  

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">        
                <table id="tablafestivos" class="table table-striped table-bordered table-condensed" style="width:100%">
                    <thead class="text-center">
                        <tr>
                            <th>Id</th>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Registrado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data as $dat) { ?>
                        <tr>
                            <td><?php echo $dat['id'] ?></td>
                            <td><?php echo $dat['fecha'] ?></td>
                            <td><?php echo $dat['descripcion'] ?></td>
                            <td><?php echo $dat['registrado_por'] ?></td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm btnEditarFestivo">Editar</button>
                                <button class="btn btn-danger btn-sm btnBorrarFestivo">Borrar</button>
                            </td>
                        </tr>
                        <?php } ?>                                
                    </tbody>        
                </table>                    
            </div>
        </div>
    </div>  

    <!-- Modal para CRUD -->
    <div class="modal fade" id="modalCRUD" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formfestivos">    
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fecha" class="col-form-label">Fecha:</label>
                            <input type="date" class="form-control" id="fecha" required>
                        </div>
                        <div class="form-group">
                            <label for="descripcion" class="col-form-label">Descripción:</label>
                            <input type="text" class="form-control" id="descripcion" required>
                        </div>
                        <div class="form-group">
                            <label for="registrado_por" class="col-form-label">Registrado por:</label>
                            <input type="text" class="form-control" id="registrado_por" required readonly
                                value="<?php echo $_SESSION['s_nombre'] . ' ' . $_SESSION['s_apellido']; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnGuardar" class="btn btn-dark">Guardar</button>
                    </div>
                </form>    
            </div>
        </div>
    </div>  
</div>
<!-- FIN del contenido principal -->

<?php require_once "vistas/parte_inferior.php"; ?>

<!-- SCRIPT JS para DataTable y CRUD -->
<script>
$(document).ready(function(){
    let id = null;
    let opcion = 1;
    let fila;

    let tablafestivos = $("#tablafestivos").DataTable({
        "columnDefs": [{
            "targets": -1,
            "data": null,
            "defaultContent": `
                <div class='text-center'>
                    <div class='btn-group'>
                        <button class='btn btn-primary btnEditarFestivo'>Editar</button>
                        <button class='btn btn-danger btnBorrarFestivo'>Borrar</button>
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

    // Botón NUEVO
    $("#btnNuevo").click(function(){
        $("#formfestivos").trigger("reset");
        $(".modal-header").css("background-color", "#1cc88a");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Nuevo festivo");
        $("#modalCRUD").modal("show");
        id = null;
        opcion = 1;
    });

    // Botón EDITAR
    $(document).on("click", ".btnEditarFestivo", function(){
        fila = $(this).closest("tr");
        id = parseInt(fila.find('td:eq(0)').text());
        let fecha = fila.find('td:eq(1)').text();
        let descripcion = fila.find('td:eq(2)').text();

        $("#fecha").val(fecha);
        $("#descripcion").val(descripcion);
        opcion = 2;

        $(".modal-header").css("background-color", "#4e73df");
        $(".modal-header").css("color", "white");
        $(".modal-title").text("Editar festivo");
        $("#modalCRUD").modal("show");
    });

    // Botón BORRAR
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
                        if (response.success) {
                            tablafestivos.row(fila).remove().draw();
                            Swal.fire('¡Eliminado!', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(){
                        Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                    }
                });
            }
        });
    });

    // Guardar (crear o editar)
    $("#formfestivos").submit(function(e){
        e.preventDefault();
        let fecha = $.trim($("#fecha").val());
        let descripcion = $.trim($("#descripcion").val());
        let registrado_por = $.trim($("#registrado_por").val());

        $.ajax({
            url: "bd/crud_festivos.php",
            type: "POST",
            dataType: "json",
            data: {fecha: fecha, descripcion: descripcion, registrado_por: registrado_por, id: id, opcion: opcion},
            success: function(data){
                let botones = `
                    <div class='text-center'>
                        <div class='btn-group'>
                            <button class='btn btn-primary btnEditarFestivo'>Editar</button>
                            <button class='btn btn-danger btnBorrarFestivo'>Borrar</button>
                        </div>
                    </div>`;
                if(opcion == 1){
                    tablafestivos.row.add([data.id, data.fecha, data.descripcion, data.registrado_por, botones]).draw();
                } else {
                    tablafestivos.row(fila).data([data.id, data.fecha, data.descripcion, data.registrado_por, botones]).draw();
                }
            }
        });
        $("#modal
