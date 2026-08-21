<?php require_once __DIR__ . '/../../config/auth.php'; ?>
<header class="header">

    <div class="header-div1">
        <img src="./assets/img/LOGO.png" alt="logo-patronato">
        <h2 class="title">Patronato Pro-mejoramiento <br><span class="title-spam">Zapotal</span></h2>
    </div>

    <div class="header-div2">
        <?php if (haySesion()): ?>
            <a href="<?= esEmpleado() ? '../../ADMIN/index.php' : 'perfil/perfil.php' ?>"
               class="perfil-btn"
               title="<?= htmlspecialchars($_SESSION['nombre']) ?>">
                <?= htmlspecialchars(strtoupper(substr($_SESSION['nombre'], 0, 1))) ?>
            </a>
            <a href="login/cerrar_sesion.php" class="header-seccion" style="text-align:center; line-height:5vh;">
                Cerrar sesión
            </a>
        <?php else: ?>
            <button class="header-seccion" id="BTN-SECION">Iniciar sesión</button>
        <?php endif; ?>

        <button class="hamburguesa" id="BTN_BURGER" onclick="toggleMenu()" aria-label="Abrir menú"
            aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<div class="overlay" id="OVERLAY" onclick="cerrarMenu()"></div>

<!--MENU NAV-->
<nav class="menu" id="MENU">
    <span class="menu-titulo">Menú</span>
    <ul>
        <li class="nav-li"><a href="#section2" onclick="cerrarMenu()">Quiénes somos</a></li>
        <li class="nav-li"><a href="#section3" onclick="cerrarMenu()">Ubicación de oficinas</a></li>
        <li class="nav-li"><a href="#section4" onclick="cerrarMenu()">Reportar queja</a></li>
    </ul>
</nav>