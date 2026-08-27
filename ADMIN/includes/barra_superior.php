<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/vinculacion.php';

// Si el empleado en sesión también es vecino (mismo DNI que un usuario),
// se le ofrece un acceso directo a su perfil de vecino en el sitio público.
$conexion = Connection();
$id_usuario_vinculado = obtenerUsuarioVinculadoAEmpleado($conexion, (int) ($_SESSION['id'] ?? 0));
?>
<header class="barra_superior">
    <!-- Contenedor principal del encabezado superior -->

    <button class="btn_menu_movil" id="btnMenuMovil" onclick="abrirSidebarAdmin()" aria-label="Abrir menú">
        <span></span><span></span><span></span>
    </button>

    <div class="div_lateral">
        <!-- Sección lateral dentro del encabezado -->

        <?php if ($id_usuario_vinculado): ?>
        <a href="../SITIO/perfil/perfil.php" class="btn-secundario btn-mi-perfil-vecino" title="Ver mi perfil de vecino">
            Mi perfil de vecino
        </a>
        <?php endif; ?>

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