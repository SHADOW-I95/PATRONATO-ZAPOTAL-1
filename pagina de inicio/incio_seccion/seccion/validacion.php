<?php

session_start();

require_once __DIR__ . '/../../../ADMIN/conexion.php';

$conexion = connection();

$nombre = trim($_POST['nombre'] ?? '');
$dni    = trim($_POST['dni'] ?? '');
$codigo = $_POST['contrasena'] ?? '';


// Control de intentos de inicio de sesión
if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = 0;
}

if (!isset($_SESSION['ultimo_intento'])) {
    $_SESSION['ultimo_intento'] = 0;
}


$LIMITE_INTENTOS = 500;
$TIEMPO_BLOQUEO  = 300;


// Verificar bloqueo por intentos fallidos
if ($_SESSION['intentos'] >= $LIMITE_INTENTOS) {

    $tiempoRestante = $TIEMPO_BLOQUEO - (time() - $_SESSION['ultimo_intento']);

    if ($tiempoRestante > 0) {

        $minutos = ceil($tiempoRestante / 60);

        die("Demasiados intentos fallidos. Intenta de nuevo en {$minutos} minuto(s).");

    } else {

        $_SESSION['intentos'] = 0;

    }

}


// Validar campos vacíos
if ($nombre === '' || $dni === '' || $codigo === '') {

    echo "Todos los campos son obligatorios";
    exit;

}


$MENSAJE_GENERICO = "DNI o contraseña incorrectos";


// Buscar usuario incluyendo el ROL
$stmt = $conexion->prepare(
    "SELECT id_usuario, NOMBRE, DNI, CONTRASENA, ESTADO, ROL 
     FROM usuarios 
     WHERE NOMBRE = ? AND DNI = ?"
);


$stmt->bind_param("ss", $nombre, $dni);

$stmt->execute();

$resultado = $stmt->get_result();



if ($fila = $resultado->fetch_assoc()) {


    // Verificar si el usuario está activo
    if ($fila['ESTADO'] !== 'ACTIVO') {

        $_SESSION['intentos']++;

        $_SESSION['ultimo_intento'] = time();

        echo $MENSAJE_GENERICO;

        exit;

    }



   // Verificar contraseña
if ($codigo === $fila['CONTRASENA']) {

    session_regenerate_id(true);

    $_SESSION['id_usuario'] = $fila['id_usuario'];
    $_SESSION['dni'] = $fila['DNI'];
    $_SESSION['nombre'] = $fila['NOMBRE'];
    $_SESSION['ROL'] = $fila['ROL'];

    $_SESSION['intentos'] = 0;

    if ($_SESSION['ROL'] == "Administrador") {
        header("Location: ../../../ADMIN/dashboard.php");
    } else {
        header("Location: ../../Pagina_inicio/index.php");
    }

    exit();
}



        // Guardar datos del usuario en la sesión
        $_SESSION['id_usuario'] = $fila['id_usuario'];
        $_SESSION['dni']        = $fila['DNI'];
        $_SESSION['nombre']     = $fila['NOMBRE'];
        $_SESSION['ROL']        = $fila['ROL'];

        $_SESSION['intentos'] = 0;



        // Redireccionar según el rol

        if ($_SESSION['ROL'] == "Administrador") {


            header("Location: ../../../ADMIN/dashboard.php");


        } else {


            header("Location: ../../Pagina_inicio/index.php");


        }


        exit();



    } else {


        $_SESSION['intentos']++;

        $_SESSION['ultimo_intento'] = time();

        echo $MENSAJE_GENERICO;


    }

$stmt->close();

$conexion->close();

?>