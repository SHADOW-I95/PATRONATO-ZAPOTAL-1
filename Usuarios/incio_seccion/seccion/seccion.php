<?php
session_start();

$usuariosValidos = [
    "marielayanorisvalle@gmail.com" => "1234",
    "otrocorreo@gmail.com"          => "5678",
    "admin@patronato.com"           => "91011"
];

$correo = $_POST['CORREO'];
$contrasena = $_POST['CONTRASEÑA'];

if(isset($usuariosValidos[$correo]) && $usuariosValidos[$correo] === $contrasena){
    $_SESSION['usuario'] = $correo;
    header("Location: dashboard.php");
    exit();
} else {
    echo "Correo o contraseña incorrectos";
}
?>
