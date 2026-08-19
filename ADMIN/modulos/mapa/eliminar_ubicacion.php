<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

// Igual que en guardar_ubicacion.php: solo el Administrador puede quitar
// una ubicación, y se valida aquí en el backend, no solo ocultando el botón.
if (!esAdministrador()) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "metodo_invalido"]);
    exit;
}

$id_vivienda = (int) ($_POST["id_vivienda"] ?? 0);

if (!$id_vivienda) {
    echo json_encode(["ok" => false, "error" => "vivienda_invalida"]);
    exit;
}

$stmtVivienda = $conexion->prepare("SELECT id_vivienda FROM viviendas WHERE id_vivienda = ?");
$stmtVivienda->execute([$id_vivienda]);

if (!$stmtVivienda->fetch()) {
    echo json_encode(["ok" => false, "error" => "vivienda_no_encontrada"]);
    exit;
}

try {

    // Se quita la ubicación (y su auditoría) sin tocar la vivienda en sí:
    // sigue existiendo con todos sus demás datos, solo deja de tener
    // coordenadas hasta que alguien la vuelva a colocar en el mapa.
    $sql = "UPDATE viviendas SET
                latitud = NULL,
                longitud = NULL,
                id_empleado_registro_ubicacion = NULL,
                fecha_registro_ubicacion = NULL,
                id_empleado_modifico_ubicacion = NULL,
                fecha_modificacion_ubicacion = NULL
            WHERE id_vivienda = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_vivienda]);

    echo json_encode(["ok" => true]);
    exit;

} catch (PDOException $e) {
    echo json_encode(["ok" => false, "error" => "error_eliminando"]);
    exit;
}
