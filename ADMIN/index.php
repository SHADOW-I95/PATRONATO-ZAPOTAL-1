<?php
require_once '../config/conexion.php';
$conexion = Connection();

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PATRONATO</title>
    <link rel="stylesheet" href="./assets/css/barras.css" />
    <link rel="stylesheet" href="./assets/css/global.css" />
    <link rel="stylesheet" href="./assets/css/dashboard.css" />
    <link rel="stylesheet" href="./assets/css/reporte.css">
    <link rel="stylesheet" href="./assets/css/factura.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>


    <div class="contenido_1">
        <?php include 'includes/barra_lateral.php'; ?>
        <div class="contenido_2">
            <?php include 'includes/barra_superior.php'; ?>
            <main class="contenido_3">
                <?php
        $modulo = $_GET['modulo'] ?? 'dashboard';
          if ($modulo=='dashboard') {
           include './modulos/dashboard/dashboard.php';
          }
          elseif ($modulo == 'usuario') {
           include './modulos/usuario/usuario2.php';
          }
          elseif ($modulo == 'agua') {
           include './modulos/agua/agua.php';
          }
          elseif ($modulo == 'cementerio') {
           include './modulos/cementerio/cementerio.php';
          }
          elseif ($modulo == 'reportes') {
           include './modulos/reportes/reportes.php';
          }
          elseif ($modulo == 'configuracion') {
           include './modulos/configuracion/configuracion.php';
          }

       ?>
            </main>
        </div>
    </div>

    <script src="./assets/js/usuario.js"></script>
    <script src="./assets/js/modal.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script src="./assets/js/ver.js"></script>
    <script src="./assets/js/agua.js"></script>


</body>

</html>

</html>