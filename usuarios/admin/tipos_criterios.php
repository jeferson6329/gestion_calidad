<?php require_once "vistas/parte_superior.php"?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!--INICIO del cont principal-->
<div class="container">
    <h1>Tipos de criterio</h1>

    <?php
    include_once '../admin/bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    $consulta = "SELECT id, nombre FROM criterios";
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
                <table id="tablacriterios" class="table table-striped table-bordered table-condensed" style="width:100%">
                    <thead class="text-center">
    <tr>
        <th>Id</th>
        <th>Nombre</th>

        <th>Acciones</th>
    </tr>
</thead>
<tbody>
    <?php foreach($data as $dat) { ?>
    <tr>
        <td><?php echo isset($dat['id']) ? $dat['id'] : ''; ?></td>
        <td><?php echo isset($dat['nombre']) ? $dat['nombre'] : ''; ?></td>

        <td class="text-center">
            <button class="btn btn-info btn-sm btnEditarcriterio">Editar</button>
            <button class="btn btn-danger btn-sm btnBorrarcriterio">Borrar</button>
        </td>
    </tr>
    <?php } ?>
</tbody>        
                </table>                    
            </div>
        </div>
    </div>  

    <!--Modal para CRUD-->
    <div class="modal fade" id="modalCRUD" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formcriterios">    
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nombre" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
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
<!--FIN del cont principal-->
<?php require_once "vistas/parte_inferior.php"?>