<?php require_once "vistas/parte_superior.php"?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!--INICIO del cont principal-->
<div class="container">
    <h1>Tipos de canal</h1>

    <?php
    include_once '../admin/bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    $consulta = "SELECT id, nombre FROM canales";
    $resultado = $conexion->prepare($consulta);
    $resultado->execute();
    $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="row">
        <div class="col-lg-12">            
            <!-- Bot¨®n Nuevo canal -->
            <button id="btnNuevo" type="button" class="btn btn-success" data-toggle="modal">Nuevo canal</button>    
        </div>    
    </div>    
    <br>  

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">        
                <table id="tablacanales" class="table table-striped table-bordered table-condensed" style="width:100%">
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
                            <td><?php echo $dat['id'] ?></td>
                            <td><?php echo htmlspecialchars($dat['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm btnEditarCanal">Editar</button>
                                <button class="btn btn-danger btn-sm btnCanalesEliminar">Eliminar</button>
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
                <form id="formcanales">    
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nombre" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" required>
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

<!-- Aqu¨ª va tu script JS para manejar las acciones -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>



    

<?php require_once "vistas/parte_inferior.php"?>
