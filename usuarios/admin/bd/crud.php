<?php
include_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Recepción de los datos enviados mediante POST desde el JS   
$documento = isset($_POST['documento']) ? $_POST['documento'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$apellido = isset($_POST['apellido']) ? $_POST['apellido'] : '';
$correo = isset($_POST['correo']) ? $_POST['correo'] : '';
$rol = isset($_POST['rol']) ? $_POST['rol'] : '';
$supervisor_id = isset($_POST['supervisor_id']) ? $_POST['supervisor_id'] : null; // ID del supervisor
$supervisor = isset($_POST['supervisor']) ? $_POST['supervisor'] : null; // Nombre del supervisor
$resetPass = isset($_POST['resetPass']) ? $_POST['resetPass'] : 0;
$requiere_cambio_clave = isset($_POST['requiere_cambio_clave']) ? $_POST['requiere_cambio_clave'] : 0;
$opcion = isset($_POST['opcion']) ? $_POST['opcion'] : '';
$id = isset($_POST['id']) ? $_POST['id'] : '';

$data = [];

switch($opcion){
    case 1: // alta
        $contraseña = password_hash($documento, PASSWORD_DEFAULT); // bcrypt por defecto
        $consulta = "INSERT INTO personas (documento, nombre, apellido, correo, contraseña, rol, supervisor_id, supervisor, requiere_cambio_clave) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$documento, $nombre, $apellido, $correo, $contraseña, $rol, $supervisor_id ? $supervisor_id : null, $supervisor, $requiere_cambio_clave]);

        // Traer el usuario recién creado con rol y supervisor
        $consulta = "SELECT id, documento, nombre, apellido, correo, rol, supervisor_id, supervisor, requiere_cambio_clave FROM personas ORDER BY id DESC LIMIT 1";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 2: // modificación
        if ($resetPass == 1) {
            $contraseña = password_hash($documento, PASSWORD_DEFAULT); // bcrypt por defecto
            $consulta = "UPDATE personas SET documento=?, nombre=?, apellido=?, correo=?, contraseña=?, rol=?, supervisor_id=?, supervisor=?, requiere_cambio_clave=? WHERE id=?";
            $resultado = $conexion->prepare($consulta);
            $resultado->execute([$documento, $nombre, $apellido, $correo, $contraseña, $rol, $supervisor_id ? $supervisor_id : null, $supervisor, $requiere_cambio_clave, $id]);
        } else {
            $consulta = "UPDATE personas SET documento=?, nombre=?, apellido=?, correo=?, rol=?, supervisor_id=?, supervisor=?, requiere_cambio_clave=? WHERE id=?";
            $resultado = $conexion->prepare($consulta);
            $resultado->execute([$documento, $nombre, $apellido, $correo, $rol, $supervisor_id ? $supervisor_id : null, $supervisor, $requiere_cambio_clave, $id]);
        }

        // Traer el usuario editado con rol y supervisor
        $consulta = "SELECT id, documento, nombre, apellido, correo, rol, supervisor_id, supervisor, requiere_cambio_clave FROM personas WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$id]);
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 3: // baja
        $consulta = "DELETE FROM personas WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$id]);
        $data = [];
        break;
}

print json_encode($data);
$conexion = null;
