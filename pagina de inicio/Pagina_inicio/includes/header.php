<<<<<<< HEAD
<?php
session_start();
?>
=======
>>>>>>> 6202cb5d8e374e69580be2edeb31c307d782f0a1

<header class="header">

        <div class="header-div1">
            <img src="./assets/img/LOGO.png" alt="logo-patronato"> <!-- Imagen del logo del patronato -->
            <h2 class="title">Patronato Pro-mejoramiento <br><span class="title-spam">Zapotal</span></h2>
        </div>

 <div class="header-div2">
            <button class="header-seccion" id="BTN-SECION">iniciar Sesion</button>

            <button class="hamburguesa" id="BTN_BURGER" onclick="toggleMenu()" aria-label="Abrir menú"
                aria-expanded="false">
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
