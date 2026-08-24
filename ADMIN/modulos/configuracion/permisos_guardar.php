<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/../../../config/bitacora.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

if (!esAdministrador()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "metodo_invalido"]);
    exit;
}

$id_rol = (int) ($_POST['id_rol'] ?? 0);
$modulos = $_POST['modulos'] ?? []; // array de claves, ej. ['usuario','agua']

// El rol Administrador (id 3) nunca se toca desde aquí: siempre tiene todo,
// sin depender de esta tabla.
if (!$id_rol || $id_rol === 3) {
    echo json_encode(["ok" => false, "error" => "rol_invalido"]);
    exit;
}

// Los módulos "empleados", "configuracion" y "registro_empleado" nunca
// pueden asignarse por esta vía aunque alguien manipule la petición:
// solo se permiten los que de verdad existen en el catálogo configurable.
$modulosValidos = $conexion->query("SELECT clave FROM modulos_sistema")->fetchAll(PDO::FETCH_COLUMN);
$modulos = array_values(array_intersect($modulos, $modulosValidos));

try {
    $conexion->beginTransaction();

    $conexion->prepare("DELETE FROM permisos_rol WHERE id_rol = ?")->execute([$id_rol]);

    if ($modulos) {
        $stmt = $conexion->prepare(
            "INSERT INTO permisos_rol (id_rol, id_modulo)
             SELECT ?, id_modulo FROM modulos_sistema WHERE clave = ?"
        );
        foreach ($modulos as $clave) {
            $stmt->execute([$id_rol, $clave]);
        }
    }

    $conexion->commit();
    registrar_actividad('configuracion', 'editó', "Actualizó los permisos del rol #{$id_rol}: " . (implode(', ', $modulos) ?: 'ningún módulo'));
    echo json_encode(["ok" => true]);
} catch (PDOException $e) {
    $conexion->rollBack();
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
}