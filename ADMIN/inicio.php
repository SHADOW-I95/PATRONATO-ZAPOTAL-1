<?php
require_once "conexion.php";
$conexion = connection();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PATRONATO</title>

 <!--=================LINKS_DE_CSS=======================-->
    <link rel="stylesheet" href="./assets/css/barra_lateral.css" />
    <link rel="stylesheet" href="./assets/css/barra_superior.css" />
    <link rel="stylesheet" href="./assets/css/global.css" />
    <link rel="stylesheet" href="./assets/css/modal.css" />
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/usuarios.css" />

    
    
 <!--==============WEA_DEL_DASHBOARD_LA_RUEDA====================-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>

  <!--================BARRA_SUPERIOR=====================-->

    <header class="barra_superior">
      <div class="barra_logo">
        <img src="./assets/img/LOGO.png" />
        <h2>Patronato pro-mejoramiento <br />zapotal</h2>
      </div>

      <div class="div_lateral">
        <img src="" alt="icnono de usuario" />
        <span>adminsitrador</span>
      </div>
    </header>


  <!--===================INCLUDES========================-->

    <?php 
     include 'usuarios/dashboard.php';
     include 'usuarios/usuario.php'; 
    ?>


 <!--=================BARRA_LATERAL==================-->

    <nav class="navbar">
      <ul class="navbar-ul">
        <li class="navbar-li"><a href="#" class="menu-item" data-page="usuario/dasboard.php">Dasbhoar</a></li>
        <li class="navbar-li"><a href="#" class="menu-item" data-page="usuario/usuario.php">usarios</a></li>
        <li class="navbar-li"><a href="#" class="menu-item" data-page="">Pagos</a></li>
        <li class="navbar-li"><a href="#" class="menu-item" data-page="">agua</a></li>
        <li class="navbar-li"><a href="#" class="menu-item" data-page="">cementerio</a></li>
        <li class="navbar-li"><a href="#" class="menu-item" data-page="">reportes</a></li>
        <li class="navbar-li"><a href="#" class="menu-item" data-page="">facturas</a></li>
      </ul>
      <ul class="navbar-ul">
        <span class="span"></span>
        <li class="navbar-li"><a href="#" class="menu-item" data-page="">configuracion</a></li>
        <li class="navbar-li"><a href="#" class="menu-item" data-page="">empleados</a></li>
      </ul>
    </nav>


  <!--=====================JS===================================-->
    <script src="./assets/js/modal.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script src="./assets/js/nose.js"></script>

  </body>
</html>
