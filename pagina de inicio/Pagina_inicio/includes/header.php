<?php
session_start(); // Inicia la sesión para manejar datos de usuario
?>

<header class="header">
    <!-- Sección izquierda: logo y título -->
    <div class="header-div1">
        <img src="./assets/img/LOGO.png" alt="logo-patronato"> <!-- Imagen del logo -->
        <h2 class="title">
            Patronato Pro-mejoramiento <br>
            <span class="title-spam">Zapotal</span>
        </h2>
    </div>

    <!-- Sección derecha: botón de sesión y menú hamburguesa -->
    <div class="header-div2">
        <button class="header-seccion" id="BTN-SECION">iniciar Sesion</button>

        <!-- Botón hamburguesa -->
        <button class="hamburguesa" id="BTN_BURGER" onclick="toggleMenu()" aria-label="Abrir menú"
            aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<!-- Capa superpuesta para cerrar el menú al hacer clic fuera -->
<div class="overlay" id="OVERLAY" onclick="cerrarMenu()"></div>

<!-- Menú de navegación lateral -->
<nav class="menu" id="MENU">
    <ul>
        <li class="nav-li"><a href="#section2" onclick="cerrarMenu()">Quienes somos</a></li>
        <li class="nav-li"><a href="#" onclick="cerrarMenu()">Pagos</a></li>
        <li class="nav-li"><a href="#section3" onclick="cerrarMenu()">Ubicacion de oficinas</a></li>
        <li class="nav-li"><a href="#section4" onclick="cerrarMenu()">Reportar queja</a></li>
    </ul>
</nav>
