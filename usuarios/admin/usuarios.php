<?php require_once "vistas/parte_superior.php"?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <h1>Todos los usuarios de la SDM</h1>
    <?php
    include_once 'bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    $roles = ['administrador', 'lider de calidad', 'agente', 'supervisor'];
    $totales_rol = [];
    foreach($roles as $rol){
        $consulta = "SELECT COUNT(*) AS total FROM personas WHERE rol = ?";
        $stmt = $conexion->prepare($consulta);
        $stmt->execute([$rol]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $totales_rol[$rol] = $fila['total'];
    }
    $filtro_rol = "";
    $parametros = [];
    if (isset($_GET['rol']) && in_array($_GET['rol'], $roles)) {
        $filtro_rol = " WHERE rol = ? ";
        $parametros[] = $_GET['rol'];
    }
    $consulta = "SELECT id, documento, nombre, apellido, correo, rol, supervisor_id, supervisor FROM personas $filtro_rol";
    $resultado = $conexion->prepare($consulta);
    $resultado->execute($parametros);
    $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

    // Consulta de supervisores con ID
    $consulta_sup = "SELECT id, nombre, apellido FROM personas WHERE rol='supervisor' OR rol='administrador'";
    $resultado_sup = $conexion->prepare($consulta_sup);
    $resultado_sup->execute();
    $supervisores = $resultado_sup->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Botones de filtro -->
    <div class="row mb-3">
        <div class="col-auto">
            <a href="usuarios.php" class="btn btn-dark">Ver todos</a>
        </div>
        <div class="col-auto">
            <a href="usuarios.php?rol=supervisor" class="btn btn-warning">
                Supervisores <span class="badge badge-light"><?php echo $totales_rol['supervisor']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="usuarios.php?rol=lider de calidad" class="btn btn-info">
                Líderes de calidad <span class="badge badge-light"><?php echo $totales_rol['lider de calidad']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="usuarios.php?rol=agente" class="btn btn-success">
                Agentes <span class="badge badge-light"><?php echo $totales_rol['agente']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <a href="usuarios.php?rol=administrador" class="btn btn-primary">
                Administradores <span class="badge badge-light"><?php echo $totales_rol['administrador']; ?></span>
            </a>
        </div>
        <div class="col-auto">
            <button id="btnNuevo" type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCRUD">Nuevo usuario</button>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table id="tablaPersonas" class="table table-striped table-bordered table-condensed" style="width:100%">
                    <thead class="text-center">
                        <tr>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Supervisor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data as $dat) { ?>
                        <tr data-id="<?php echo $dat['id']; ?>"
                            data-documento="<?php echo $dat['documento']; ?>"
                            data-nombre="<?php echo $dat['nombre']; ?>"
                            data-apellido="<?php echo $dat['apellido']; ?>"
                            data-correo="<?php echo $dat['correo']; ?>"
                            data-rol="<?php echo $dat['rol']; ?>"
                            data-supervisor_id="<?php echo $dat['supervisor_id']; ?>"
                            data-supervisor="<?php echo $dat['supervisor']; ?>"
                        >
                            <td><?php echo $dat['documento'] ?></td>
                            <td><?php echo $dat['nombre'] ?></td>
                            <td><?php echo $dat['apellido'] ?></td>
                            <td><?php echo $dat['correo'] ?></td>
                            <td>
                                <?php
                                switch($dat['rol']) {
                                    case 'lider de calidad': echo '<span class="badge badge-info">Líder de calidad</span>'; break;
                                    case 'agente': echo '<span class="badge badge-success">Agente</span>'; break;
                                    case 'supervisor': echo '<span class="badge badge-warning">Supervisor</span>'; break;
                                    case 'administrador': echo '<span class="badge badge-primary">Administrador</span>'; break;
                                    default: echo $dat['rol'];
                                }
                                ?>
                            </td>
                            <td>
                                <?php echo !empty($dat['supervisor']) ? $dat['supervisor'] : '-'; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm btnEditar">Editar</button>
                                <button class="btn btn-danger btn-sm btnBorrar">Borrar</button>
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
                <form id="formPersonas">
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <div class="form-group">
                            <label for="documento" class="col-form-label">Documento:</label>
                            <input type="text" class="form-control" id="documento" name="documento" pattern="\d*" required>
                        </div>
                        <div class="form-group">
                            <label for="nombre" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido" class="col-form-label">Apellido:</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>
                        <div class="form-group">
                            <label for="correo" class="col-form-label">Correo:</label>
                            <input type="email" class="form-control" id="correo" name="correo" required>
                        </div>
                        <div class="form-group">
                            <label for="rol" class="col-form-label">Rol:</label>
                            <select class="form-control" id="rol" name="rol" required>
                                <option value="agente" selected>Agente</option>
                                <option value="lider de calidad">Líder de calidad</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        <div class="form-group" id="restablecerClaveGroup" style="display:none;">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="restablecerClave" name="restablecerClave">
                                <label class="form-check-label" for="restablecerClave">¿Restablecer contraseña a número de documento?</label>
                            </div>
                            <small id="helpClave" class="form-text text-muted"></small>
                        </div>
                        <div class="form-group">
                            <label for="supervisor" class="col-form-label">Supervisor:</label>
                            <select class="form-control" id="supervisor_id" name="supervisor_id" required>
                                <option value="">-- Seleccione un supervisor --</option>
                                <?php foreach($supervisores as $sup){ ?>
                                    <option value="<?php echo $sup['id']; ?>">
                                        <?php echo $sup['nombre'] . ' ' . $sup['apellido']; ?>
                                    </option>
                                <?php } ?>
                                
                            </select>
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
<?php require_once "vistas/parte_inferior.php"?>