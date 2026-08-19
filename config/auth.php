<?php
// Funciones de sesión para saber si hay alguien conectado y de qué tipo.
// $_SESSION['tipo'] vale 'usuario' o 'empleado'.
// Para empleados, además se guarda $_SESSION['id_rol'] (2 = Empleado, 3 = Administrador).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function haySesion(): bool
{
    return isset($_SESSION['tipo'], $_SESSION['id']);
}

function esEmpleado(): bool
{
    return haySesion() && $_SESSION['tipo'] === 'empleado';
}

function esUsuarioComun(): bool
{
    return haySesion() && $_SESSION['tipo'] === 'usuario';
}

// Solo el rol Administrador (id_rol = 3). Un Empleado normal (id_rol = 2)
// puede entrar al panel, pero esto es false para él.
function esAdministrador(): bool
{
    return esEmpleado() && (int) ($_SESSION['id_rol'] ?? 0) === 3;
}