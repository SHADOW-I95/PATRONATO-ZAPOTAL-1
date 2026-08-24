<?php

require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";

if (!tienePermiso('usuario')) {
    http_response_code(403);
    die("No tienes permiso para hacer esto.");
}

$conexion = Connection();
$id_usuario = (int)($_GET['id'] ?? 0);

if ($id_usuario <= 0) {
    die("ID de usuario no válido.");
}

// Datos para la bitácora, ANTES de borrarlo (después ya no existirían)
$stmtDatos = $conexion->prepare("SELECT nombre, apellido, dni FROM usuarios WHERE id_usuario = ?");
$stmtDatos->execute([$id_usuario]);
$usuarioAEliminar = $stmtDatos->fetch();

try {

    $conexion->beginTransaction();

    // 1. Eliminar los detalles de los pagos
    $sql = "DELETE d
            FROM detalle_pago_agua d
            INNER JOIN pagos_agua p
                ON d.id_pago_agua = p.id_pago_agua
            INNER JOIN viviendas v
                ON p.id_vivienda = v.id_vivienda
            WHERE v.id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);


    // 2. Eliminar los pagos
    $sql = "DELETE p
            FROM pagos_agua p
            INNER JOIN viviendas v
                ON p.id_vivienda = v.id_vivienda
            WHERE v.id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);


    // 3. Eliminar los reportes del usuario
    $sql = "DELETE FROM reportes
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);


    // 4. Eliminar las viviendas
    $sql = "DELETE FROM viviendas
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);


    // 5. Eliminar el usuario
    $sql = "DELETE FROM usuarios
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);


    // Confirmar todos los cambios
    $conexion->commit();

    if ($usuarioAEliminar) {
        registrar_actividad('usuario', 'eliminó', "Eliminó al usuario {$usuarioAEliminar['nombre']} {$usuarioAEliminar['apellido']} (DNI {$usuarioAEliminar['dni']}), junto con sus viviendas, pagos y reportes");
    }

    header("Location: ../../index.php?modulo=usuario");
    exit;

} catch (Exception $e) {

    // Deshacer todos los cambios si algo falla
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    echo "Error: " . $e->getMessage();
}