<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/../../../config/bitacora.php";
$conexion = Connection();

if (!esAdministrador()) {
    http_response_code(403);
    die("No tienes permisos para hacer esto.");
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../../index.php?modulo=configuracion");
    exit;
}

$nombre_patronato  = trim($_POST['nombre_patronato'] ?? '');
$telefono_contacto = trim($_POST['telefono_contacto'] ?? '');
$direccion         = trim($_POST['direccion'] ?? '');
$banco_nombre      = trim($_POST['banco_nombre'] ?? '');
$banco_cuenta      = trim($_POST['banco_cuenta'] ?? '');
$banco_titular     = trim($_POST['banco_titular'] ?? '');

if ($nombre_patronato === '') {
    header("Location: ../../index.php?modulo=configuracion&error=nombre_vacio");
    exit;
}

// ===== Logo nuevo (opcional) =====
$logo_path_nuevo = null;

if (!empty($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['logo'];

    if ($archivo['size'] > 3 * 1024 * 1024) {
        header("Location: ../../index.php?modulo=configuracion&error=logo_muy_grande");
        exit;
    }

    $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    if (!isset($tiposPermitidos[$mime])) {
        header("Location: ../../index.php?modulo=configuracion&error=logo_invalido");
        exit;
    }

    $extension = $tiposPermitidos[$mime];
    $nombreArchivo = 'logo_' . bin2hex(random_bytes(4)) . '.' . $extension;
    // El logo se guarda del lado de SITIO (assets/img), porque tanto el
    // sitio público como el ADMIN lo referencian relativo a esa carpeta.
    $rutaDestino = __DIR__ . '/../../../../SITIO/assets/img/' . $nombreArchivo;

    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        $logo_path_nuevo = 'assets/img/' . $nombreArchivo;
    }
}

try {
    if ($logo_path_nuevo) {
        $stmt = $conexion->prepare(
            "UPDATE configuracion_general SET
                nombre_patronato = ?, telefono_contacto = ?, direccion = ?,
                banco_nombre = ?, banco_cuenta = ?, banco_titular = ?, logo_path = ?
             WHERE id = 1"
        );
        $stmt->execute([$nombre_patronato, $telefono_contacto, $direccion, $banco_nombre, $banco_cuenta, $banco_titular, $logo_path_nuevo]);
    } else {
        $stmt = $conexion->prepare(
            "UPDATE configuracion_general SET
                nombre_patronato = ?, telefono_contacto = ?, direccion = ?,
                banco_nombre = ?, banco_cuenta = ?, banco_titular = ?
             WHERE id = 1"
        );
        $stmt->execute([$nombre_patronato, $telefono_contacto, $direccion, $banco_nombre, $banco_cuenta, $banco_titular]);
    }

    registrar_actividad('configuracion', 'editó', "Actualizó los datos generales del patronato");

    header("Location: ../../index.php?modulo=configuracion&mensaje=actualizado");
    exit;

} catch (PDOException $e) {
    header("Location: ../../index.php?modulo=configuracion&error=error_guardando");
    exit;
}
