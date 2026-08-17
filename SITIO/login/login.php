<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="seccion.css">
    <meta charset="UTF-8"> <!-- Configuración de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Diseño adaptable a móviles -->
    <title>Iniciar Sesión</title> <!-- Título de la página -->

    <!-- Estilos propios -->
    <link rel="stylesheet" href="seccion.css">

    <!-- Librería de íconos Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Contenedor principal -->
    <div class="contenedor">

        <!-- Sección del logo -->
        <div class="logo">
            <img src="../assets/img.LOGO.PNG" alt="Logo">
            <h2>PATRONATO<br></h2>
        </div>

        <form class="formulario" method="post" action="procesar_login.php" id="formLogin">
            <img src="img/logo_-removebg-preview.png" alt="Logo"> <!-- Imagen del logo -->
            <h2>PATRONATO<br>DE AGUA</h2> <!-- Texto del título -->
        </div>

        <!-- Formulario de inicio de sesión -->
        <form class="formulario" method="post" action="validacion.php" id="formLogin">
            <div>
                <h1>Iniciar sesión</h1> <!-- Encabezado -->
                <p>Accede a tu cuenta para continuar.</p> <!-- Texto descriptivo -->
            </div>

            <!-- Campo de nombre -->
            <input type="text" placeholder="NOMBRE" name="nombre" id="nombre" required minlength="3">
            <span class="error" id="errorNombre"></span> <!-- Mensaje de error para nombre -->

            <!-- Campo de DNI -->
            <input type="text" placeholder="DNI" name="dni" id="dni" required maxlength="20">
            <span class="error" id="errorDni"></span> <!-- Mensaje de error para DNI -->

            <!-- Campo de contraseña con ícono para mostrar/ocultar -->
            <div class="password-box">
                <input type="password" id="password" placeholder="CODIGO DE ACCESO" name="contrasena" required minlength="2">
                <span onclick="mostrarPassword()"><i class="fa-solid fa-eye"></i></span>
            </div>
            <span class="error" id="errorPassword"></span>

            <span class="error" id="errorServidor"></span>

            <button id="Ingresar" type="submit">Ingresar</button>

        </form>
    </div>

    <script src="login.js"></script>
</body>
</html>