<?php
require_once __DIR__ . '/../../config/auth.php';
// Módulo actual, para resaltar en qué sección está parado el usuario
$modulo_actual = $_GET['modulo'] ?? 'dashboard';
?>
<div class="barraLateral">
    <!-- Contenedor principal de toda la barra lateral -->

    <div class="iconos">
        <!-- Sección superior con logo e identificación -->
        <img src="./assets/img/LOGO.png"> <!-- Imagen del logo -->
        <p>Patronato el Zapotal</p> <!-- Texto con el nombre de la organización -->
    </div>

    <nav class="barraNavegacion">
        <!-- Menú de navegación principal -->

        <a href="?modulo=dashboard" class="<?= $modulo_actual === 'dashboard' ? 'activo' : '' ?>">Panel</a>
        <a href="?modulo=usuario" class="<?= $modulo_actual === 'usuario' ? 'activo' : '' ?>">Usuarios</a>
        <a href="?modulo=agua" class="<?= $modulo_actual === 'agua' ? 'activo' : '' ?>">Agua</a>
        <a href="?modulo=reportes" class="<?= $modulo_actual === 'reportes' ? 'activo' : '' ?>">Reportes</a>
        <a href="?modulo=mapa" class="<?= $modulo_actual === 'mapa' ? 'activo' : '' ?>">Mapa</a>
        <!-- Mapa: visible para empleados y administradores -->

        <hr> <!-- Línea divisoria para separar secciones -->

        <?php if (esAdministrador()): ?>
        <!-- Solo el Administrador ve y puede entrar al módulo de Empleados -->
        <a href="?modulo=empleados" class="<?= $modulo_actual === 'empleados' ? 'activo' : '' ?>">Empleados</a>
        <?php endif; ?>

        <a href="?modulo=configuracion" class="<?= $modulo_actual === 'configuracion' ? 'activo' : '' ?>">Configuracion</a>
    </nav>
</div>