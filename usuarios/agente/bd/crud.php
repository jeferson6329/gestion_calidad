
<?php
include_once '/xampp/htdocs/Gestion_calidad/usuarios/agente/bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();
// Recepción de los datos enviados mediante POST desde el JS   
$documento = (isset($_POST['documento'])) ? $_POST['documento'] : '';
$nombre = (isset($_POST['nombre'])) ? $_POST['nombre'] : '';
$apellido = (isset($_POST['apellido'])) ? $_POST['apellido'] : '';
$correo = (isset($_POST['correo'])) ? $_POST['correo'] : '';
$contraseña = (isset($_POST['contraseña'])) ? $_POST['contraseña'] : '';
if ($contraseña != '') {
    $contraseña = md5($contraseña);
}
$rol = (isset($_POST['rol'])) ? $_POST['rol'] : '';
$opcion = (isset($_POST['opcion'])) ? $_POST['opcion'] : '';
$id = (isset($_POST['id'])) ? $_POST['id'] : '';

switch($opcion){
    case 1: //alta
        $consulta = "INSERT INTO personas (documento, nombre, apellido, correo, contraseña, rol) VALUES('$documento', '$nombre', '$apellido', '$correo', '$contraseña', '$rol') ";			
        $resultado = $conexion->prepare($consulta);
        $resultado->execute(); 

        $consulta = "SELECT documento, nombre, apellido, correo, contraseña, rol FROM personas ORDER BY id DESC LIMIT 1";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 2: //modificación
        if ($contraseña != '') {
            // Si se envió una nueva contraseña, actualiza todo
            $consulta = "UPDATE personas SET documento='$documento', nombre='$nombre', apellido='$apellido', correo='$correo', contraseña='$contraseña', rol='$rol' WHERE id='$id' ";
        } else {
            // Si no se envió contraseña, no la actualices
            $consulta = "UPDATE personas SET documento='$documento', nombre='$nombre', apellido='$apellido', correo='$correo', rol='$rol' WHERE id='$id' ";
        }
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();        

        $consulta = "SELECT documento, nombre, apellido, correo, contraseña, rol FROM personas WHERE id='$id' ";       
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);
        break;       
    case 3://baja
        $consulta = "DELETE FROM personas WHERE id='$id' ";		
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();                           
        $data = []; // <-- Importante para devolver JSON válido
        break;        
}

if ($resultado->errorCode() != '00000') {
    print_r($resultado->errorInfo());
}
print json_encode($data);
$conexion = null;