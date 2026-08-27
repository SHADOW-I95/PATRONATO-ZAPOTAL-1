<?php
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../config/configuracion_general.php';
$config_general = obtenerConfiguracionGeneral();
// Módulo actual, para resaltar en qué sección está parado el usuario
$modulo_actual = $_GET['modulo'] ?? 'dashboard';
$modulos = modulosVisibles(); // solo los módulos que el rol actual puede ver
?>
<div class="barraLateral" id="barraLateralAdmin">
    <!-- Contenedor principal de toda la barra lateral -->

    <div class="iconos">
        <!-- Sección superior con logo e identificación -->
        <img src="../SITIO/<?= htmlspecialchars($config_general['logo_path'] ?? 'assets/img/LOGO.png') ?>"> <!-- Logo del patronato, editable en Configuración -->
        <p><?= htmlspecialchars($config_general['nombre_patronato'] ?? 'Patronato el Zapotal') ?></p>
    </div>

    <nav class="barraNavegacion">
        <!-- Menú de navegación principal: se arma según los permisos del rol -->

        <?php foreach ($modulos as $clave => $datos): ?>
        <a href="<?= $datos['href'] ?>" class="<?= $modulo_actual === $clave ? 'activo' : '' ?>"><?= htmlspecialchars($datos['texto']) ?></a>
        <?php endforeach; ?>

        <?php if (esAdministrador()): ?>
        <hr> <!-- Línea divisoria: de aquí para abajo, solo Administrador -->

        <a href="?modulo=empleados" class="<?= $modulo_actual === 'empleados' ? 'activo' : '' ?>">Empleados</a>
        <a href="?modulo=registro_empleado" class="<?= $modulo_actual === 'registro_empleado' ? 'activo' : '' ?>">Registro de empleado</a>
        <?php endif; ?>

        <a href="?modulo=configuracion" class="<?= $modulo_actual === 'configuracion' ? 'activo' : '' ?>">Configuracion</a>
    </nav>
</div>