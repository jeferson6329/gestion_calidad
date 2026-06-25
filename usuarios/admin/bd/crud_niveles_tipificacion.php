<?php
include_once '../bd/conexion.php';
$objeto = new Conexion();
$conexion = $objeto->Conectar();

// Recoger datos del POST
$id             = isset($_POST['id']) ? $_POST['id'] : '';
$texto          = isset($_POST['texto']) ? $_POST['texto'] : '';
$canal_id       = isset($_POST['canal_id']) ? $_POST['canal_id'] : '';
$nivel_padre_id = (isset($_POST['nivel_padre_id']) && $_POST['nivel_padre_id'] !== '') ? $_POST['nivel_padre_id'] : null;
$registrado_por = isset($_POST['registrado_por']) ? $_POST['registrado_por'] : '';
$opcion         = isset($_POST['opcion']) ? $_POST['opcion'] : '';

switch($opcion){
    case 1: // Crear
        $consulta = "INSERT INTO niveles_tipificacion (texto, canal_id, nivel_padre_id, registrado_por) 
                     VALUES (?, ?, ?, ?)";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$texto, $canal_id, $nivel_padre_id, $registrado_por]);
        $id = $conexion->lastInsertId();
        break;

    case 2: // Editar
        $consulta = "UPDATE niveles_tipificacion 
                     SET texto=?, canal_id=?, nivel_padre_id=? 
                     WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$texto, $canal_id, $nivel_padre_id, $id]);
        break;

    case 3: // Eliminar
        $consulta = "DELETE FROM niveles_tipificacion WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $success = $resultado->execute([$id]);
        echo json_encode(['success' => $success]);
        $conexion = null;
        exit;
}

// Traer canal_nombre y nivel_padre_texto para devolverlos al DataTable
$canal_nombre = '';
if ($canal_id) {
    $stmt = $conexion->prepare("SELECT nombre FROM canales WHERE id=?");
    $stmt->execute([$canal_id]);
    $canal_nombre = $stmt->fetchColumn();
}

$nivel_padre_texto = '';
if ($nivel_padre_id) {
    $stmt = $conexion->prepare("SELECT texto FROM nivel_tipificacion1 WHERE id=?");
    $stmt->execute([$nivel_padre_id]);
    $nivel_padre_texto = $stmt->fetchColumn();
}

// Respuesta final en JSON
echo json_encode([
    'id' => $id,
    'texto' => $texto,
    'canal_id' => $canal_id,
    'canal_nombre' => $canal_nombre,
    'nivel_padre_id' => $nivel_padre_id,
    'nivel_padre_texto' => $nivel_padre_texto,
    'registrado_por' => $registrado_por
]);

$conexion = null;
?>
