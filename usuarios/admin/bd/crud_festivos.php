<?php
include_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
$registrado_por = isset($_POST['registrado_por']) ? $_POST['registrado_por'] : '';
$opcion = isset($_POST['opcion']) ? intval($_POST['opcion']) : 0;

switch($opcion){
    case 1: // Crear
        $consulta = "INSERT INTO dias_festivos (fecha, descripcion, registrado_por) VALUES (?, ?, ?)";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$fecha, $descripcion, $registrado_por]);
        $id = $conexion->lastInsertId();
        echo json_encode([
            'id' => $id,
            'fecha' => $fecha,
            'descripcion' => $descripcion,
            'registrado_por' => $registrado_por
        ]);
        break;

    case 2: // Editar
        $consulta = "UPDATE dias_festivos SET fecha=?, descripcion=?, registrado_por=? WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$fecha, $descripcion, $registrado_por, $id]);
        echo json_encode([
            'id' => $id,
            'fecha' => $fecha,
            'descripcion' => $descripcion,
            'registrado_por' => $registrado_por
        ]);
        break;

    case 3: // Eliminar
        $consulta = "DELETE FROM dias_festivos WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $success = $resultado->execute([$id]);
        if($success){
            echo json_encode(['success' => true, 'message' => 'El registro ha sido eliminado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el registro.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Opción no válida']);
        break;
}

$conexion = null;
?>
