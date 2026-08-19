<?php require_once __DIR__ . '/../../config/auth.php'; ?>
<div class="barraLateral">
    <!-- Contenedor principal de toda la barra lateral -->

    <div class="iconos">
        <!-- Sección superior con logo e identificación -->
        <img src="./assets/img/LOGO.png"> <!-- Imagen del logo -->
        <p>Patronato el Zapotal</p> <!-- Texto con el nombre de la organización -->
    </div>

    <nav class="barraNavegacion">
        <!-- Menú de navegación principal -->

        <a href="?modulo=dashboard">Panel</a> <!-- Enlace al módulo Dashboard -->
        <a href="?modulo=usuario"> Usuarios</a> <!-- Enlace al módulo Usuarios -->
        <a href="?modulo=agua"> Agua</a> <!-- Enlace al módulo Agua -->
        <a href="?modulo=reportes"> Reportes</a> <!-- Enlace al módulo Reportes -->
        <a href="?modulo=mapa"> Mapa</a> <!-- Enlace al módulo Mapa (empleados y administradores) -->

        <hr> <!-- Línea divisoria para separar secciones -->

        <?php if (esAdministrador()): ?>
        <!-- Solo el Administrador ve y puede entrar al módulo de Empleados -->
        <a href="?modulo=empleados">Empleados</a>
        <?php endif; ?>

        <a href="?modulo=configuracion"> Configuracion</a> <!-- Enlace al módulo Configuración -->
    </nav>
</div>