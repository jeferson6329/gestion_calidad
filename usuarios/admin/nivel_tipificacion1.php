<?php 
require_once "vistas/parte_superior.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['s_nombre'], $_SESSION['s_apellido'])) {
    header('Location: login.php');
    exit;
}
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="container">
    <h1>Nivel Tipificación 1</h1>

    <?php
    include_once '../admin/bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    // Obtener lista de registros
    $consulta = "SELECT id, texto, registrado_por, fecha_creacion FROM nivel_tipificacion1";
    $resultado = $conexion->prepare($consulta);
    $resultado->execute();
    $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="row mb-3">
        <div class="col-lg-12">
            <button id="btnNuevo" type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCRUD">Nuevo</button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tabla_nivel_tipificacion1" class="table table-striped table-bordered" style="width:100%">
            <thead class="text-center">
                <tr>
                    <th>ID</th>
                    <th>Texto</th>
                    <th>Registrado por</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $dat) { ?>
                <tr>
                    <td><?= $dat['id'] ?></td>
                    <td><?= htmlspecialchars($dat['texto']) ?></td>
                    <td><?= htmlspecialchars($dat['registrado_por']) ?></td>
                    <td><?= $dat['fecha_creacion'] ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btnEditar" style="background-color:#4e73df; color:white;" 
                                data-id="<?= $dat['id'] ?>"
                                data-texto="<?= htmlspecialchars($dat['texto'], ENT_QUOTES) ?>"
                        >Editar</button>
                        <button class="btn btn-danger btn-sm btnBorrar" data-id="<?= $dat['id'] ?>">Borrar</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal CRUD -->
    <div class="modal fade" id="modalCRUD" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="formNivelTipificacion1">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Nuevo Registro</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <div class="form-group">
                            <label for="texto">Texto:</label>
                            <input type="text" class="form-control" id="texto" name="texto" required>
                        </div>
                        <input type="hidden" id="registrado_por" name="registrado_por" 
                               value="<?= $_SESSION['s_nombre'] . ' ' . $_SESSION['s_apellido'] ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--FIN del cont principal-->

<script src="nivel_tipificacion1.js"></script>
<?php require_once "vistas/parte_inferior.php"; ?>
