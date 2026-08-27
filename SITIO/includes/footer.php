<?php
require_once __DIR__ . '/../../config/configuracion_general.php';
$config_general = obtenerConfiguracionGeneral();
?>
<footer>
    <!-- Contenedor principal del footer -->
    <div class="flex">

        <!-- Sección 1: Logo y mensaje -->
        <div class="footer-1">
            <img src="./<?= htmlspecialchars($config_general['logo_path'] ?? 'assets/img/LOGO.png') ?>" alt="Logo <?= htmlspecialchars($config_general['nombre_patronato'] ?? '') ?>">
            <p>Comprometidos con el bienestar de nuestra comunidad</p>
        </div>

        <!-- Sección 2: Enlaces rápidos -->
        <div class="footer2">
            <h2>Enlaces rápidos</h2>
            <nav>
                <ul>
                    <li><a href="#section2">Quiénes somos</a></li>
                    <li><a href="#section4">Reportar queja</a></li>
                    <li><a href="#section3">Ubicación de oficinas</a></li>
                    <li><a href="login/login.php">Iniciar sesión</a></li>
                </ul>
            </nav>
        </div>

        <!-- Sección 3: Información de contacto -->
        <div class="footer3">
            <h2>Información</h2>
            <p><span class="icono-footer icono-mapa"></span> <?= htmlspecialchars($config_general['direccion'] ?? '—') ?></p>
            <p><span class="icono-footer icono-telefono"></span> <?= htmlspecialchars($config_general['telefono_contacto'] ?? '—') ?></p>
            <p><span class="icono-footer icono-correo"></span> patronato@gmail.com</p>
        </div>

    </div>

    <div class="footer-legal">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($config_general['nombre_patronato'] ?? 'Patronato el Zapotal') ?>. Todos los derechos reservados.
    </div>
</footer>