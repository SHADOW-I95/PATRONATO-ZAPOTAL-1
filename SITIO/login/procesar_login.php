<?php
require_once __DIR__ . '/../../config/conexion.php'; // conexión a la base de datos
require_once __DIR__ . '/../../config/auth.php';     // autenticación de usuarios

$conexion = Connection();

$nombre = trim($_POST['nombre'] ?? '');   // nombre ingresado
$dni    = trim($_POST['dni'] ?? '');      // DNI ingresado
$codigo = trim($_POST['contrasena'] ?? ''); // código de acceso

// Control simple de intentos fallidos (por sesión)
if (!isset($_SESSION['intentos']))       $_SESSION['intentos'] = 0;
if (!isset($_SESSION['ultimo_intento'])) $_SESSION['ultimo_intento'] = 0;

$LIMITE_INTENTOS = 5;       // máximo intentos permitidos
$TIEMPO_BLOQUEO  = 300;     // tiempo de bloqueo en segundos (5 minutos)

if ($_SESSION['intentos'] >= $LIMITE_INTENTOS) {
    $tiempoRestante = $TIEMPO_BLOQUEO - (time() - $_SESSION['ultimo_intento']);
    if ($tiempoRestante > 0) {
        $minutos = ceil($tiempoRestante / 60);
        die("Demasiados intentos fallidos. Intenta de nuevo en {$minutos} minuto(s).");
    }
    $_SESSION['intentos'] = 0; // reinicia intentos si ya pasó el bloqueo
}

// Validación de campos vacíos
if ($nombre === '' || $dni === '' || $codigo === '') {
    echo "Todos los campos son obligatorios";
    exit;
}

$MENSAJE_GENERICO = "Nombre, DNI o código incorrectos";

/**
 * El formulario solo tiene un campo de "nombre", pero en la base de datos
 * está separado en nombre + apellido. La persona puede escribir cualquiera
 * de las dos formas (solo el nombre, o el nombre completo), así que se
 * aceptan ambas en vez de comparar solo contra la columna "nombre".
 */
function coincideNombre(string $ingresado, string $nombreBD, string $apellidoBD): bool
{
    $ingresado = mb_strtolower(trim($ingresado));

    $variantes = [
        mb_strtolower(trim($nombreBD)),
        mb_strtolower(trim($nombreBD . ' ' . $apellidoBD)),
    ];

    return in_array($ingresado, $variantes, true);
}

// Se busca primero en empleados: si una persona está registrada como empleado,
// debe entrar al panel administrativo aunque por coincidencia también exista
// como usuario común.
$stmt = $conexion->prepare(
    "SELECT id_empleado AS id, nombre, apellido, dni
     FROM empleados
     WHERE dni = ? AND codigo = ?"
);
$stmt->execute([$dni, $codigo]);
$empleado = $stmt->fetch();


if ($empleado && strcasecmp($nombre, $empleado['nombre']) === 0) {

if ($empleado && coincideNombre($nombre, $empleado['nombre'], $empleado['apellido'])) {


    session_regenerate_id(true);

    $_SESSION['tipo']     = 'empleado';
    $_SESSION['id']       = $empleado['id'];
    $_SESSION['nombre']   = $empleado['nombre'];
    $_SESSION['apellido'] = $empleado['apellido'];
    $_SESSION['dni']      = $empleado['dni'];
    $_SESSION['intentos'] = 0;

    header("Location: ../../ADMIN/index.php"); // redirige al panel administrativo
    exit;
}

// Si no es empleado, se busca como usuario común
$stmt = $conexion->prepare(
    "SELECT id_usuario AS id, nombre, apellido, dni
     FROM usuarios
     WHERE dni = ? AND codigo = ?"
);
$stmt->execute([$dni, $codigo]);
$usuario = $stmt->fetch();


if ($usuario && strcasecmp($nombre, $usuario['nombre']) === 0) {

if ($usuario && coincideNombre($nombre, $usuario['nombre'], $usuario['apellido'])) {


    session_regenerate_id(true);

    $_SESSION['tipo']     = 'usuario';
    $_SESSION['id']       = $usuario['id'];
    $_SESSION['nombre']   = $usuario['nombre'];
    $_SESSION['apellido'] = $usuario['apellido'];
    $_SESSION['dni']      = $usuario['dni'];
    $_SESSION['intentos'] = 0;

    header("Location: ../perfil/perfil.php"); // redirige al perfil de usuario
    exit;
}

// Si no coincide en ninguna tabla, aumenta intentos y muestra error
$_SESSION['intentos']++;
$_SESSION['ultimo_intento'] = time();
echo $MENSAJE_GENERICO;
