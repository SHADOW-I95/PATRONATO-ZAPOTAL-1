<?php require_once __DIR__ . '/../../config/auth.php'; ?> 
<!-- Incluye el archivo de autenticación para manejar sesiones y permisos -->

<header class="header">

    <!-- Sección izquierda: logo y título -->
    <div class="header-div1">
        <img src="./assets/img/LOGO.png" alt="logo-patronato"> <!-- Logo del patronato -->
        <h2 class="title">
            Patronato Pro-mejoramiento <br>
            <span class="title-spam">Zapotal</span> <!-- Subtítulo destacado -->
        </h2>
    </div>

    <!-- Sección derecha: botones de sesión y menú -->
    <div class="header-div2">
        <?php if (haySesion()): ?> <!-- Si hay sesión activa -->
            <a href="<?= esEmpleado() ? '../../ADMIN/index.php' : 'perfil/perfil.php' ?>"
               class="perfil-btn"
               title="<?= htmlspecialchars($_SESSION['nombre']) ?>">
                <!-- Muestra la inicial del nombre del usuario -->
                <?= htmlspecialchars(strtoupper(substr($_SESSION['nombre'], 0, 1))) ?>
            </a>
            <a href="login/cerrar_sesion.php" class="header-seccion" style="text-align:center; line-height:5vh;">
                Cerrar sesión <!-- Botón para cerrar sesión -->
            </a>
        <?php else: ?> <!-- Si NO hay sesión activa -->
            <button class="header-seccion" id="BTN-SECION">Iniciar sesión</button>
        <?php endif; ?>

        <!-- Botón hamburguesa -->
        <button class="hamburguesa" id="BTN_BURGER" onclick="toggleMenu()" aria-label="Abrir menú"
            aria-expanded="false">
            <span></span> <!-- Barra 1 -->
            <span></span> <!-- Barra 2 -->
            <span></span> <!-- Barra 3 -->
        </button>
    </div>
</header>

<!-- Capa oscura para cerrar el menú al hacer clic fuera -->
<div class="overlay" id="OVERLAY" onclick="cerrarMenu()"></div>

<!-- Menú de navegación lateral -->
<nav class="menu" id="MENU">
    <ul>
        <li class="nav-li"><a href="#section2" onclick="cerrarMenu()">Quienes somos</a></li>
        <li class="nav-li"><a href="#section4" onclick="cerrarMenu()">Reportar queja</a></li>
        <li class="nav-li"><a href="#section3" onclick="cerrarMenu()">Ubicacion de oficinas</a></li>
    </ul>
</nav>
