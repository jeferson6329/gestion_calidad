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
    <h1>Niveles de Tipificaci√≥n</h1>

    <?php
    include_once '../admin/bd/conexion.php';
    $objeto = new Conexion();
    $conexion = $objeto->Conectar();

    // Obtener niveles con nombre de canal
$consulta = "SELECT n.id, n.texto, n.canal_id, n.registrado_por, 
                    c.nombre AS canal_nombre, 
                    np.texto AS nivel_padre_texto,
                    n.nivel_padre_id
             FROM niveles_tipificacion n 
             LEFT JOIN canales c ON n.canal_id = c.id
             LEFT JOIN nivel_tipificacion1 np ON n.nivel_padre_id = np.id";

    $resultado = $conexion->prepare($consulta);
    $resultado->execute();
    $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
$consulta_niveles = "SELECT id, texto FROM nivel_tipificacion1";
$resultado_niveles = $conexion->prepare($consulta_niveles);
$resultado_niveles->execute();
$niveles_padre = $resultado_niveles->fetchAll(PDO::FETCH_ASSOC);


    // Obtener lista de canales
    $consulta_canales = "SELECT id, nombre FROM canales";
    $resultado_canales = $conexion->prepare($consulta_canales);
    $resultado_canales->execute();
    $canales = $resultado_canales->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="row mb-3">
        <div class="col-lg-12">
            <button id="btnNuevo" type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCRUD">Nuevo</button>
        </div>
    </div>

<div class="table-responsive">
    <table id="tablaNivelesTipificacion" class="table table-striped table-bordered" style="width:100%">
        <thead class="text-center">
            <tr>
                <th>Id</th>
                <th>Texto</th>
                <th>Canal</th>
                <th>Nivel Padre</th>
                <th>Registrado por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $dat) { ?>
            <tr>
                <td><?= $dat['id'] ?></td>
                <td><?= htmlspecialchars($dat['texto']) ?></td>
                <td><?= $dat['canal_nombre'] ? htmlspecialchars($dat['canal_nombre']) : '<span class="text-muted">Sin canal</span>' ?></td>
                <td><?= $dat['nivel_padre_texto'] ? htmlspecialchars($dat['nivel_padre_texto']) : '<span class="text-muted">Sin nivel padre</span>' ?></td>
                <td><?= htmlspecialchars($dat['registrado_por']) ?></td>
                <td class="text-center">
                    <button class="btn btn-sm btnEditarCanal" style="background-color:#4e73df; color:white;" 
                            data-id="<?= $dat['id'] ?>"
                            data-texto="<?= htmlspecialchars($dat['texto'], ENT_QUOTES) ?>"
                            data-canal_id="<?= $dat['canal_id'] ?>"
                            data-nivel_padre_id="<?= $dat['nivel_padre_id'] ?>"
                    >Editar</button>
                    <button class="btn btn-danger btn-sm btnBorrarCanal" data-id="<?= $dat['id'] ?>">Borrar</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>


    <!-- Modal CRUD -->
    <div class="modal fade" id="modalCRUD" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="formNivelesTipificacion">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Nuevo Nivel</h5>
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
<div class="form-group">
    <label for="nivel_padre_id">Pertenece a:</label>
    <select class="form-control" id="nivel_padre_id" name="nivel_padre_id">
        <option value="">Seleccione nivel de tipificaci®Æn</option>
        <?php foreach($niveles_padre as $nivel): ?>
            <option value="<?= $nivel['id'] ?>"><?= htmlspecialchars($nivel['texto']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
                        <div class="form-group">
                            <label for="canal_id">Canal:</label>
                            <select class="form-control" id="canal_id" name="canal_id" required>
                                <option value="">Seleccione canal</option>
                                <?php foreach($canales as $canal): ?>
                                    <option value="<?= $canal['id'] ?>"><?= htmlspecialchars($canal['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="registrado_por">Registrado por:</label>
                            <input type="text" class="form-control" id="registrado_por" name="registrado_por" 
                                   value="<?= $_SESSION['s_nombre'] . ' ' . $_SESSION['s_apellido'] ?>" readonly>
                        </div>
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

<script>
// Al hacer click en editar, llena el modal con los datos
$('.btnEditarCanal').on('click', function() {
    $('#modalLabel').text('Editar Nivel');
    $('#id').val($(this).data('id'));
    $('#texto').val($(this).data('texto'));
    $('#canal_id').val($(this).data('canal_id'));
    $('#modalCRUD').modal('show');
});

// Al hacer click en nuevo, limpia el modal
$('#btnNuevo').on('click', function() {
    $('#modalLabel').text('Nuevo Nivel');
    $('#formNivelesTipificacion')[0].reset();
    $('#id').val('');
});
</script>

<?php require_once "vistas/parte_inferior.php"; ?>
