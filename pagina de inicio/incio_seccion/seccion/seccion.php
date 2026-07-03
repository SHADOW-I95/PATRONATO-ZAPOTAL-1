<?php
session_start();

$usuariosValidos = [
    "marielayanorisvalle@gmail.com" => "12345",
    "otrocorreo@gmail.com"          => "5678",
    "admin@patronato.com"           => "91011"
];

$correo = $_POST['correo'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

if (isset($usuariosValidos[$correo]) && $usuariosValidos[$correo] === $contrasena) {
    $_SESSION['usuario'] = $correo;
    header("Location: ../../Pagina_inicio/index.php");
    exit();
} else {
    echo "Correo o contraseña incorrectos";
}
?>
