<?php
session_start();
if(!isset($_SESSION['s_id'])){
    header("Location: index.php");
    exit;
}

include_once 'bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$mensaje = '';
$clave_actualizada = false;

if(isset($_POST['guardar_clave'])){
    $clave1 = isset($_POST['clave1']) ? trim($_POST['clave1']) : '';
    $clave2 = isset($_POST['clave2']) ? trim($_POST['clave2']) : '';

    // Validación de formato de contraseña
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

    if($clave1 === '' || $clave2 === ''){
        $mensaje = "Debe llenar ambos campos";
    } elseif($clave1 !== $clave2){
        $mensaje = "Las contraseñas no coinciden";
    } elseif(!preg_match($regex, $clave1)){
        $mensaje = "La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula, un número y un caracter especial.";
    } else {
        $passHash = password_hash($clave1, PASSWORD_DEFAULT); // bcrypt por defecto
        $fechaActual = date('Y-m-d H:i:s');

        $update = $conexion->prepare("
            UPDATE personas
            SET contraseña = ?, 
                fecha_ultimo_cambio_clave = ?, 
                requiere_cambio_clave = 0
            WHERE id = ?
        ");
        $update->execute([$passHash, $fechaActual, $_SESSION['s_id']]);

        $clave_actualizada = true;
        // No redirige aún, muestra el mensaje primero
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="plugins/sweetalert2/sweetalert2.min.css">
</head>
<body>
    <div class="container-login">
        <div class="wrap-login">
            <form method="post" autocomplete="off">
                <span class="login-form-title">Digite una nueva contraseña</span>

                <?php if($mensaje != ''): ?>
<script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
<script>
    Swal.fire({
        type: 'warning',
        title: 'Error',
        text: '<?= addslashes($mensaje) ?>',
        confirmButtonText: 'Aceptar'
    });
</script>
<?php endif; ?>

                <div class="wrap-input100">
                    <input class="input100" type="password" name="clave1" placeholder="Nueva contraseña" required>
                </div>

                <div class="wrap-input100">
                    <input class="input100" type="password" name="clave2" placeholder="Confirmar contraseña" required>
                </div>

                <div class="wrap-login-form-btn">
                    <div class="login-form-bgbtn"></div>
                    <button type="submit" name="guardar_clave" class="login-form-btn">Guardar Contraseña</button>
                </div>
            </form>
        </div>
    </div>

    <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <?php if($clave_actualizada): ?>
    <script>
        Swal.fire({
            type: 'success',
            title: '¡Contraseña actualizada correctamente!',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            // Redirige según el rol
            <?php
            $rol = $_SESSION['s_rol'];
            if($rol === "administrador"){
                echo "window.location.href = '/usuarios/admin/index.php';";
            } elseif($rol === "lider de calidad"){
                echo "window.location.href = '/usuarios/lider_calidad/index.php';";
            } elseif($rol === "agente"){
                echo "window.location.href = '/usuarios/agente/index.php';";
            } elseif($rol === "supervisor") {
                echo "window.location.href = '/usuarios/supervisor/index.php';";
            }
            ?>
        });
    </script>
    <?php endif; ?>
</body>
</html>
