<?php
$usuarioLogueado = isset($_SESSION['nombre']);
$nombreUsuario = $usuarioLogueado ? htmlspecialchars($_SESSION['nombre']) : '';
$inicial = $usuarioLogueado ? strtoupper(substr($nombreUsuario, 0, 1)) : '';
?>

<header class="header">

        <div class="header-div1">
            <img src="./assets/img/LOGO.png" alt="logo-patronato">
            <h2 class="title">Patronato Pro-mejoramiento <br><span class="title-spam">Zapotal</span></h2>
        </div>

        <div class="header-div2">

            <?php if ($usuarioLogueado): ?>
                <a href="perfil.php" class="avatar-perfil" title="<?php echo $nombreUsuario; ?>">
                    <span class="avatar-circulo"><?php echo $inicial; ?></span>
                </a>
            <?php else: ?>
                <button class="header-seccion" id="BTN-SECION" onclick="window.location.href='../incio_seccion/seccion/seccion.html'">iniciar Sesion</button>
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
        <ul>
            <li class="nav-li"><a href="" onclick="cerrarMenu()">Quienes somos</a></li>
            <li class="nav-li"><a href="" onclick="cerrarMenu()">Pagos</a></li>
            <li class="nav-li"><a href="" onclick="cerrarMenu()">Ubicacion de oficinas</a></li>
            <li class="nav-li"><a href="" onclick="cerrarMenu()">Reportar queja</a></li>
        </ul>
</nav>