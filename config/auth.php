<?php
// Funciones de sesión para saber si hay alguien conectado y de qué tipo.
// $_SESSION['tipo'] vale 'usuario' o 'empleado' — nunca un texto libre como
// "Administrador" (eso fue lo que traía el código viejo y no combinaba con
// ninguna columna real de la base de datos).

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