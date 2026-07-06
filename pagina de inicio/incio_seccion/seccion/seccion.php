<?php

session_start();

require_once __DIR__ . '/../../../ADMIN/conexion.php';
$conexion = connection();

$nombre = trim($_POST['nombre'] ?? '');
$dni = trim($_POST['dni'] ?? '');
$codigo = $_POST['codigo'] ?? '';

if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = 0;
}

if ($_SESSION['intentos'] >= 300) {
    die("Demasiados intentos fallidos. Intenta de nuevo más tarde.");
}

if ($nombre === '' || $dni === '' || $codigo === '') {
    echo "Todos los campos son obligatorios";
    exit;
}

$stmt = $conexion->prepare("SELECT ID_USUARIO, NOMBRE, CODIGO, ESTADO FROM usuarios WHERE NOMBRE = ? AND DNI = ?");
$stmt->bind_param("ss", $nombre, $dni);
$stmt->execute();
$resultado = $stmt->get_result();

if ($fila = $resultado->fetch_assoc()) {

    if ($fila['ESTADO'] !== 'ACTIVO') {
        echo "Usuario inactivo. Contacta al patronato.";
        exit;
    }

    if (password_verify($codigo, $fila['CODIGO'])) {
        session_regenerate_id();
        $_SESSION['dni'] = $fila['DNI'];
        $_SESSION['nombre'] = $fila['NOMBRE'];
        $_SESSION['intentos'] = 0;

        header("Location:/./Pagina_inicio/index.php");
        exit();
    } else {
        $_SESSION['intentos']++;
        echo "Correo o contraseña incorrectos";
    }
} else {
    $_SESSION['intentos']++;
    echo "Correo o contraseña incorrectos";
}

$stmt->close();
$conexion->close();
?>