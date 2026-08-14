<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Configuración de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Diseño adaptable -->
    
    <!-- Archivos CSS para cada sección -->
    <link rel="stylesheet" href="css/inicio.css">
    <link rel="stylesheet" href="css/burger.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/galeria_img.css">
    <link rel="stylesheet" href="css/section1.css">
    <link rel="stylesheet" href="css/section2.css">
    <link rel="stylesheet" href="css/section3.css">
    <link rel="stylesheet" href="css/section4.css">
    <link rel="stylesheet" href="css/footer.css">

    <title>Patronato</title> <!-- Título de la página -->
</head>
<body>

    <?php 
    // Incluye los fragmentos PHP que contienen cada sección de la página
    include 'includes/header.php';   // Encabezado con logo y menú
    include 'includes/section1.php'; // Primera sección (presentación)
    include 'includes/section2.php'; // Segunda sección (información)
    include 'includes/section4.php'; // Cuarta sección (formulario de reportes)
    include 'includes/section3.php'; // Tercera sección (ubicación/contacto)
    ?>

    <!-- Archivo JS para interactividad -->
    <script src="inicio.js"></script>
    
</body>
</html>
