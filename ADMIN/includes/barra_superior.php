<?php require_once __DIR__ . '/../../config/auth.php'; ?>
<header class="barra_superior">
    <!-- Contenedor principal del encabezado superior -->

    <button class="btn_menu_movil" id="btnMenuMovil" onclick="abrirSidebarAdmin()" aria-label="Abrir menú">
        <span></span><span></span><span></span>
    </button>

    <div class="div_lateral">
        <!-- Sección lateral dentro del encabezado -->

        <div class="icono_usuario">
            <!-- Antes era una <img> con src vacío; con la inicial del nombre alcanza y no depende de subir una foto -->
            <?= htmlspecialchars(strtoupper(substr($_SESSION['nombre'] ?? '?', 0, 1))) ?>
        </div>

        <span class="nombre_empleado"><?= htmlspecialchars(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?></span>
        <!-- Nombre real del empleado que inició sesión -->

        <a href="../SITIO/login/cerrar_sesion.php" class="btn_cerrar_sesion">Cerrar sesión</a>
    </div>
</header>

<div class="overlay_sidebar_admin" id="overlaySidebarAdmin" onclick="cerrarSidebarAdmin()"></div>