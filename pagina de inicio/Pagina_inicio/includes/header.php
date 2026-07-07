<?php
$usuarioLogueado = isset($_SESSION['nombre']); /* Verifica si el usuario ha iniciado sesión comprobando si la variable de sesión 'nombre' está establecida */
$nombreUsuario = $usuarioLogueado ? htmlspecialchars($_SESSION['nombre']) : ''; /* Si el usuario ha iniciado sesión, obtiene el nombre del usuario de la variable de sesión y lo almacena en $nombreUsuario. Si no, establece $nombreUsuario como una cadena vacía. */
$inicial = $usuarioLogueado ? strtoupper(substr($nombreUsuario, 0, 1)) : ''; /* Si el usuario ha iniciado sesión, obtiene la primera letra del nombre del usuario, la convierte a mayúscula y la almacena en $inicial. Si no, establece $inicial como una cadena vacía. */
?>

<header class="header">

        <div class="header-div1">
            <img src="./assets/img/LOGO.png" alt="logo-patronato"> <!-- Imagen del logo del patronato -->
            <h2 class="title">Patronato Pro-mejoramiento <br><span class="title-spam">Zapotal</span></h2>
        </div>

        <div class="header-div2">

            <?php if ($usuarioLogueado): ?> <!-- Si el usuario ha iniciado sesión -->
                <a href="perfil.php" class="avatar-perfil" title="<?php echo $nombreUsuario; ?>"> <!-- Enlace a la página de perfil del usuario, con un título que muestra el nombre del usuario -->
                    <span class="avatar-circulo"><?php echo $inicial; ?></span> <!-- Muestra la inicial del nombre del usuario dentro de un círculo, que sirve como avatar -->
                </a>
            <?php else: ?>
                <button class="header-seccion" id="BTN-SECION" onclick="window.location.href='../incio_seccion/seccion/seccion.html'">iniciar Sesion</button> <!-- Botón que redirige a la página de inicio de sesión si el usuario no ha iniciado sesión -->
            <?php endif; ?>

            <button class="hamburguesa" id="BTN_BURGER" onclick="toggleMenu()" aria-label="Abrir menú" 
                aria-expanded="false"> <!-- Botón de menú hamburguesa -->
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

<div class="overlay" id="OVERLAY" onclick="cerrarMenu()"></div> <!-- Capa superpuesta para cerrar el menú -->

    <!--MENU NAV-->
<nav class="menu" id="MENU">
        <ul>
            <li class="nav-li"><a href="" onclick="cerrarMenu()">Quienes somos</a></li>
            <li class="nav-li"><a href="" onclick="cerrarMenu()">Pagos</a></li>
            <li class="nav-li"><a href="" onclick="cerrarMenu()">Ubicacion de oficinas</a></li>
            <li class="nav-li"><a href="" onclick="cerrarMenu()">Reportar queja</a></li>
        </ul>
</nav>