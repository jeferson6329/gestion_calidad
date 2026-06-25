<?php
session_start();
include_once 'conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Obtener datos del formulario
$correo = isset($_POST['correo']) ? $_POST['correo'] : '';
$contraseña = isset($_POST['contraseña']) ? $_POST['contraseña'] : '';

// Función para registrar auditoría
function registrarAuditoria($conexion, $tabla, $registro_id, $accion, $usuario_id, $usuario_nombre, $detalles = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
    $dispositivo = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
    
    $sql = "INSERT INTO auditoria (tabla, registro_id, accion, usuario_id, usuario_nombre, ip, dispositivo, detalles) 
            VALUES (:tabla, :registro_id, :accion, :usuario_id, :usuario_nombre, :ip, :dispositivo, :detalles)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':tabla' => $tabla,
        ':registro_id' => $registro_id,
        ':accion' => $accion,
        ':usuario_id' => $usuario_id,
        ':usuario_nombre' => $usuario_nombre,
        ':ip' => $ip,
        ':dispositivo' => $dispositivo,
        ':detalles' => $detalles
    ]);
}

// Buscar usuario por correo
$consulta = "SELECT * FROM personas WHERE correo = :correo";
$resultado = $conexion->prepare($consulta);
$resultado->bindParam(':correo', $correo, PDO::PARAM_STR);
$resultado->execute();

if ($resultado->rowCount() >= 1) {
    $data = $resultado->fetch(PDO::FETCH_ASSOC);

    // Verificar contraseña
    if (password_verify($contraseña, $data['contraseña'])) {

        // Guardar variables de sesión
        $_SESSION["s_id"] = $data['id'];
        $_SESSION["s_nombre"] = $data['nombre'];
        $_SESSION["s_apellido"] = $data['apellido'];
        $_SESSION["s_usuario"] = $data['correo'];
        $_SESSION["s_rol"] = $data['rol'];

        // Registrar auditoría login exitoso
        registrarAuditoria($conexion, 'personas', $data['id'], 'LOGIN_EXITOSO', $data['id'], $data['nombre'], 'Usuario inició sesión correctamente');

        // === Verificar si debe cambiar contraseña ===
        $forzarCambio = false;

        if (!empty($data['requiere_cambio_clave']) && $data['requiere_cambio_clave'] == 1) {
            $forzarCambio = true;
        } else {
            if (!empty($data['fecha_ultimo_cambio_clave'])) {
                $fechaUltimoCambio = new DateTime($data['fecha_ultimo_cambio_clave']);
                $hoy = new DateTime();
                $diferencia = $fechaUltimoCambio->diff($hoy);

                if ($diferencia->m >= 3 || $diferencia->y > 0) {
                    $forzarCambio = true;
                }
            } else {
                $forzarCambio = true;
            }
        }

        // Respuesta al cliente
        echo json_encode([
            "success" => true,
            "rol" => $data['rol'],
            "forzar_cambio" => $forzarCambio
        ]);

    } else {
        // Contraseña incorrecta
        registrarAuditoria($conexion, 'personas', $data['id'], 'LOGIN_FALLIDO', $data['id'], $data['nombre'], 'Contraseña incorrecta');
        echo json_encode([
            "success" => false,
            "mensaje" => "Credenciales incorrectas"
        ]);
    }
} else {
    // Usuario no encontrado
    registrarAuditoria($conexion, 'personas', null, 'LOGIN_FALLIDO', null, $correo, 'Usuario no encontrado');
    echo json_encode([
        "success" => false,
        "mensaje" => "Credenciales incorrectas"
    ]);
}

$conexion = null;
?>
