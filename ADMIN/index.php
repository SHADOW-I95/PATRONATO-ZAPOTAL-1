<?php
require_once "conexion.php";
$conexion = connection();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./assets/css/global.css">
    <link rel="stylesheet" href="./assets/css/barra_lateral.css">
    <link rel="stylesheet" href="./assets/css/barra_superior.css">
    <link rel="stylesheet" href="./assets/css/modal.css">
    <link rel="stylesheet" href="./assets/css/usuarios.css">
</head>
<body>


<?php 
  include 'layout/barra_superior.php';
  include 'layout/barra_lateral.php';
  include 'usuarios/usuario.php';
  include 'usuarios/dashboard.php';
?>

<script src="./assets/js/modal.js"></script>
<script src="./assets/js/script.js"></script>

</body>
</html>