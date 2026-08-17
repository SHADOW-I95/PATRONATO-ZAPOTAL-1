<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "metodo_invalido"]);
    exit;
}

// =======================
// DATOS DEL USUARIO
// =======================

$dni              = trim($_POST["DNI"]);
$codigo           = trim($_POST["codigo"]);
$nombre           = trim($_POST["nombre"]);
$apellido         = trim($_POST["apellido"]);
$fecha_nacimiento = !empty($_POST["fecha_nacimiento"]) ? $_POST["fecha_nacimiento"] : null;
$telefono         = !empty($_POST["telefono"]) ? $_POST["telefono"] : null;

// =======================
// VALIDACIONES ANTES DE GUARDAR NADA
// =======================

// ¿Ya existe un usuario con ese DNI?
$stmtDni = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE dni = ?");
$stmtDni->execute([$dni]);
if ($stmtDni->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "dni_duplicado"]);
    exit;
}

// ¿Ya existe un usuario con ese código de acceso?
$stmtCodigo = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE codigo = ?");
$stmtCodigo->execute([$codigo]);
if ($stmtCodigo->fetchColumn() > 0) {
    echo json_encode(["ok" => false, "error" => "codigo_duplicado"]);
    exit;
}

// ¿Alguna de las viviendas que se quieren agregar ya existe (mismo número, mismo sector)?
if (!empty($_POST["vivienda"])) {

    $stmtViviendaExiste = $conexion->prepare(
        "SELECT COUNT(*) FROM viviendas WHERE numero_vivienda = ? AND id_sector = ?"
    );

    foreach ($_POST["vivienda"] as $v) {

        if (empty($v["numero"]) || empty($v["sector"])) {
            continue; // el "required" del formulario ya obliga a llenarlos
        }

        $stmtViviendaExiste->execute([$v["numero"], $v["sector"]]);

        if ($stmtViviendaExiste->fetchColumn() > 0) {
            echo json_encode(["ok" => false, "error" => "vivienda_duplicada"]);
            exit;
        }
    }
}

// =======================
// YA VALIDADO: GUARDAR USUARIO Y VIVIENDAS
// =======================

try {

    $conexion->beginTransaction();

    $sqlUsuario = "
        INSERT INTO usuarios
        (
            dni,
            nombre,
            apellido,
            fecha_nacimiento,
            telefono,
            codigo
        )
        VALUES
        (?, ?, ?, ?, ?, ?)";

    $stmtUsuario = $conexion->prepare($sqlUsuario);

    $stmtUsuario->execute([
        $dni,
        $nombre,
        $apellido,
        $fecha_nacimiento,
        $telefono,
        $codigo
    ]);

    // Obtener el ID del usuario recién creado
    $id_usuario = $conexion->lastInsertId();

    // =======================
    // GUARDAR VIVIENDAS
    // =======================

    if (!empty($_POST["vivienda"])) {

        $sqlVivienda = "
            INSERT INTO viviendas
            (
                id_usuario,
                id_sector,
                id_servicio,
                numero_vivienda,
                cuota,
                id_estado_pago
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?
            )
        ";

        $stmtVivienda = $conexion->prepare($sqlVivienda);

        foreach ($_POST["vivienda"] as $v) {

            $stmtVivienda->execute([
                $id_usuario,
                $v["sector"],
                $v["servicio"],
                $v["numero"],
                $v["cuota"],
                $v["estado"]
            ]);
        }
    }

    $conexion->commit();

    echo json_encode(["ok" => true]);
    exit;

} catch (PDOException $e) {

    $conexion->rollBack();

    // Código 23000 = choque con una restricción UNIQUE (por si dos personas
    // guardan casi al mismo tiempo con el mismo dato, aunque ya se validó arriba)
    if ($e->getCode() == 23000) {
        echo json_encode(["ok" => false, "error" => "dato_duplicado"]);
        exit;
    }

    echo json_encode(["ok" => false, "error" => "error_guardando"]);
    exit;
}