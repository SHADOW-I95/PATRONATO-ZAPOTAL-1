<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Patronato el Zapotal</title>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="sesion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="contenedor">

        <!-- Panel izquierdo: marca (se oculta en pantallas pequeñas) -->
        <div class="panel-marca">
            <img src="../assets/img/LOGO.png" alt="Logo Patronato el Zapotal">
            <h2>PATRONATO<br><span>EL ZAPOTAL</span></h2>
            <p>Consulta el estado de tus viviendas, tus pagos de agua y reporta cualquier problema desde tu cuenta.</p>
        </div>

        <!-- Panel derecho: formulario -->
        <div class="panel-formulario">

            <!-- Logo visible solo en móvil, arriba del formulario -->
            <div class="logo-movil">
                <img src="../assets/img/LOGO.png" alt="Logo">
                <span>PATRONATO EL ZAPOTAL</span>
            </div>

            <form class="formulario" method="post" action="procesar_login.php" id="formLogin">
                <div>
                    <h1>Iniciar sesión</h1>
                    <p class="subtitulo">Accede a tu cuenta para continuar.</p>
                </div>

                <!-- Mensaje de error general del servidor: arriba del todo para que se note -->
                <div class="alerta-error" id="errorServidor" role="alert"></div>

                <!-- Campo nombre -->
                <label for="nombre">Nombre</label>
                <input type="text" placeholder="Como aparece en tu registro" name="nombre" id="nombre" required minlength="3" autocomplete="name">
                <span class="error" id="errorNombre"></span>

                <!-- Campo DNI -->
                <label for="dni">DNI</label>
                <input type="text" placeholder="Sin guiones ni espacios" name="dni" id="dni" required maxlength="20" inputmode="numeric">
                <span class="error" id="errorDni"></span>

                <!-- Campo contraseña con ícono para mostrar/ocultar -->
                <label for="password">Código de acceso</label>
                <div class="password-box">
                    <input type="password" id="password" placeholder="El código que te entregó el patronato" name="contrasena" required minlength="2" autocomplete="current-password">
                    <span onclick="mostrarPassword()"><i class="fa-solid fa-eye"></i></span>
                </div>
                <span class="error" id="errorPassword"></span>

                <!-- Botón de envío -->
                <button id="Ingresar" type="submit">Ingresar</button>
                <a class="salirbtn" href="../index.php">Volver al inicio</a>
            </form>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>