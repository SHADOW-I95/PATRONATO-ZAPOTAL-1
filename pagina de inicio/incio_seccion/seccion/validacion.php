<?php

session_start(); // Inicia la sesión para manejar variables de usuario y control de intentos

require_once __DIR__ . '/../../../ADMIN/conexion.php'; // Importa la conexión a la base de datos
$conexion = connection(); // Crea la conexión

// Obtiene y limpia los datos enviados por el formulario
$nombre = trim($_POST['nombre'] ?? '');
$dni    = trim($_POST['dni'] ?? '');
$codigo = $_POST['contrasena'] ?? '';

// =======================
// CONTROL DE INTENTOS DE INICIO DE SESIÓN
// =======================

// Inicializa variables de sesión si no existen
if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = 0;
}
if (!isset($_SESSION['ultimo_intento'])) {
    $_SESSION['ultimo_intento'] = 0;
}

// Define límites
$LIMITE_INTENTOS = 500; // Máximo de intentos permitidos
$TIEMPO_BLOQUEO  = 300; // Tiempo de bloqueo en segundos (5 minutos)

// Verifica si el usuario está bloqueado por demasiados intentos
if ($_SESSION['intentos'] >= $LIMITE_INTENTOS) {
    $tiempoRestante = $TIEMPO_BLOQUEO - (time() - $_SESSION['ultimo_intento']);

    if ($tiempoRestante > 0) {
        $minutos = ceil($tiempoRestante / 60);
        die("Demasiados intentos fallidos. Intenta de nuevo en {$minutos} minuto(s).");
    } else {
        $_SESSION['intentos'] = 0; // Reinicia intentos si ya pasó el tiempo de bloqueo
    }
}

// =======================
// VALIDACIÓN DE CAMPOS
// =======================
if ($nombre === '' || $dni === '' || $codigo === '') {
    echo "Todos los campos son obligatorios";
    exit;
}

$MENSAJE_GENERICO = "DNI o contraseña incorrectos";

// =======================
// BÚSQUEDA DEL USUARIO
// =======================

// Prepara consulta para buscar usuario con nombre y DNI
$stmt = $conexion->prepare(
    "SELECT id_usuario, NOMBRE, DNI, CONTRASENA, ESTADO, ROL 
     FROM usuarios 
     WHERE NOMBRE = ? AND DNI = ?"
);

$stmt->bind_param("ss", $nombre, $dni); // Asigna parámetros
$stmt->execute(); // Ejecuta consulta
$resultado = $stmt->get_result(); // Obtiene resultados

// =======================
// VALIDACIÓN DE USUARIO
// =======================
if ($fila = $resultado->fetch_assoc()) {

    // Verifica si el usuario está activo
    if ($fila['ESTADO'] !== 'ACTIVO') {
        $_SESSION['intentos']++;
        $_SESSION['ultimo_intento'] = time();
        echo $MENSAJE_GENERICO;
        exit;
    }

    // Verifica contraseña
    if ($codigo === $fila['CONTRASENA']) {
        session_regenerate_id(true); // Regenera ID de sesión por seguridad

        // Guarda datos del usuario en la sesión
        $_SESSION['id_usuario'] = $fila['id_usuario'];
        $_SESSION['dni']        = $fila['DNI'];
        $_SESSION['nombre']     = $fila['NOMBRE'];
        $_SESSION['ROL']        = $fila['ROL'];

        $_SESSION['intentos'] = 0; // Reinicia intentos

        // Redirecciona según el rol
        if ($_SESSION['ROL'] == "Administrador") {
            header("Location: ../../../ADMIN/dashboard.php");
        } else {
            header("Location: ../../Pagina_inicio/index.php");
        }
        exit();
    }

    // Si la contraseña no coincide, aumenta intentos y muestra error
    $_SESSION['intentos']++;
    $_SESSION['ultimo_intento'] = time();
    echo $MENSAJE_GENERICO;

} else {
    // Usuario no encontrado
    $_SESSION['intentos']++;
    $_SESSION['ultimo_intento'] = time();
    echo $MENSAJE_GENERICO;
}

// Cierra recursos
$stmt->close();
$conexion->close();

?>
