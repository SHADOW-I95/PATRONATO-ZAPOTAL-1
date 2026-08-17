<?php
require_once __DIR__ . '/../../config/auth.php'; // incluye el sistema de autenticación

session_unset();   // elimina todas las variables de sesión
session_destroy(); // destruye la sesión actual

header("Location: ../index.php"); // redirige al inicio después de cerrar sesión
exit; // asegura que el script se detenga aquí
?>
