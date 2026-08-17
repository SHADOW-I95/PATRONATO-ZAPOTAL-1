<?php
require_once '../config/conexion.php';

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf8" /> <!-- Configuración de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <!-- Diseño adaptable -->
    <title>PATRONATO</title> <!-- Título de la página -->

    <!-- Archivos CSS para estilos -->
    <link rel="stylesheet" href="./assets/css/barras.css" />
    <link rel="stylesheet" href="./assets/css/global.css" />
    <link rel="stylesheet" href="./assets/css/dashboard.css" />
    <link rel="stylesheet" href="./assets/css/reporte.css">
    <link rel="stylesheet" href="./assets/css/factura.css">

    <!-- Librería Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Contenedor principal -->
    <div class="contenido_1">
        <?php include 'includes/barra_lateral.php'; ?> <!-- Barra lateral -->

        <div class="contenido_2">
            <?php include 'includes/barra_superior.php'; ?> <!-- Barra superior -->

            <main class="contenido_3">
                <?php
                // Obtiene el módulo desde la URL, por defecto "dashboard"
                $modulo = $_GET['modulo'] ?? 'dashboard';

                // Según el módulo, incluye el archivo correspondiente
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
                elseif ($modulo == 'empleados') {
                    include './modulos/empleados/empleados.php';
                }
                elseif ($modulo == 'configuracion') {
                    include './modulos/configuracion/configuracion.php';
                }
                ?>
            </main>
        </div>
    </div>

    <!-- Archivos JS para funcionalidades -->
    <script src="./assets/js/usuario.js"></script>
    <script src="./assets/js/reporte.js"></script>
    <script src="./assets/js/modal.js"></script>
    <script src="./assets/js/dashboard.js"></script>
    <script src="./assets/js/ver.js"></script>

</body>
</html>
