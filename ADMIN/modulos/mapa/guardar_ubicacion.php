<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/../../../config/bitacora.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

// Solo el Administrador puede registrar/editar ubicaciones. No basta con
// ocultar el formulario en el HTML: se valida de nuevo aquí, en el backend,
// por si alguien intenta llamar a este endpoint directamente.
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

// La vivienda debe existir de verdad: el mapa nunca crea viviendas nuevas,
// solo coloca coordenadas sobre una que ya existe en la base de datos.
$stmtVivienda = $conexion->prepare("SELECT latitud FROM viviendas WHERE id_vivienda = ?");
$stmtVivienda->execute([$id_vivienda]);
$vivienda = $stmtVivienda->fetch();

if (!$vivienda) {
    echo json_encode(["ok" => false, "error" => "vivienda_no_encontrada"]);
    exit;
}

// Validación de coordenadas: numéricas y dentro del rango geográfico real.
if (!isset($_POST["latitud"]) || !isset($_POST["longitud"])
    || !is_numeric($_POST["latitud"]) || !is_numeric($_POST["longitud"])) {
    echo json_encode(["ok" => false, "error" => "coordenadas_invalidas"]);
    exit;
}

$latitud  = (float) $_POST["latitud"];
$longitud = (float) $_POST["longitud"];

if ($latitud < -90 || $latitud > 90 || $longitud < -180 || $longitud > 180) {
    echo json_encode(["ok" => false, "error" => "coordenadas_fuera_de_rango"]);
    exit;
}

// Si la vivienda todavía no tenía ubicación, esta es la primera vez que se
// registra: se guarda quién y cuándo. Si ya tenía, se cuenta como
// modificación y se conserva quién la registró originalmente.
$esPrimerRegistro = $vivienda["latitud"] === null;
$id_empleado       = (int) ($_SESSION['id'] ?? 0);

try {

    if ($esPrimerRegistro) {
        $sql = "UPDATE viviendas SET
                    latitud = ?,
                    longitud = ?,
                    id_empleado_registro_ubicacion = ?,
                    fecha_registro_ubicacion = NOW(),
                    id_empleado_modifico_ubicacion = NULL,
                    fecha_modificacion_ubicacion = NULL
                WHERE id_vivienda = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$latitud, $longitud, $id_empleado, $id_vivienda]);
    } else {
        $sql = "UPDATE viviendas SET
                    latitud = ?,
                    longitud = ?,
                    id_empleado_modifico_ubicacion = ?,
                    fecha_modificacion_ubicacion = NOW()
                WHERE id_vivienda = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$latitud, $longitud, $id_empleado, $id_vivienda]);
    }

    registrar_actividad('mapa', $esPrimerRegistro ? 'creó' : 'editó', ($esPrimerRegistro ? "Registró" : "Actualizó") . " la ubicación de la vivienda #{$id_vivienda}");

    echo json_encode(["ok" => true]);
    exit;

} catch (PDOException $e) {
    echo json_encode(["ok" => false, "error" => "error_guardando"]);
    exit;
}
