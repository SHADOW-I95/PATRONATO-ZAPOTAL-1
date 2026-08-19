<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- configuración de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- diseño adaptable a móviles -->
    <title>Iniciar Sesión</title> <!-- título de la página -->
    <link rel="stylesheet" href="sesion.css"> <!-- hoja de estilos personalizada -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> <!-- íconos Font Awesome -->
</head>
<body>
    <div class="contenedor">

        <!-- Logo y título institucional -->
        <div class="logo">
            <img src="../assets/img/LOGO.png" alt="Logo">
            <h2>PATRONATO<br>DE AGUA</h2>
        </div>

        <!-- Formulario de inicio de sesión -->
        <form class="formulario" method="post" action="procesar_login.php" id="formLogin">
            <div>
                <h1>Iniciar sesión</h1> <!-- encabezado -->
                <p>Accede a tu cuenta para continuar.</p> <!-- texto descriptivo -->
            </div>

            <!-- Campo nombre -->
            <input type="text" placeholder="NOMBRE" name="nombre" id="nombre" required minlength="3">
            <span class="error" id="errorNombre"></span>

            <!-- Campo DNI -->
            <input type="text" placeholder="DNI" name="dni" id="dni" required maxlength="20">
            <span class="error" id="errorDni"></span>

            <!-- Campo contraseña con ícono para mostrar/ocultar -->
            <div class="password-box">
                <input type="password" id="password" placeholder="CODIGO DE ACCESO" name="contrasena" required minlength="2">
                <span onclick="mostrarPassword()"><i class="fa-solid fa-eye"></i></span>
            </div>
            <span class="error" id="errorPassword"></span>

            <!-- Mensaje de error general del servidor -->
            <span class="error" id="errorServidor"></span>

            <!-- Botón de envío -->
            <button id="Ingresar" type="submit">Ingresar</button>
            <a class="salirbtn" href="../index.php">Salir</a>
        </form>
    </div>

    <!-- Script con validaciones -->
    <script src="login.js"></script>
</body>
</html>
