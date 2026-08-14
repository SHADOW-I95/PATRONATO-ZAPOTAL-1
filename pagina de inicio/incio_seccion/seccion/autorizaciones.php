<?php

// Iniciar sesión si todavía no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Verifica si el usuario es Administrador
function esAdministrador(){
    return isset($_SESSION['ROL']) && $_SESSION['ROL'] == "Administrador";
}


// Verifica si el usuario es Usuario
function esUsuario(){
    return isset($_SESSION['ROL']) && $_SESSION['ROL'] == "Usuario";
}


// Permite acceso solo al Administrador
function tieneAccesoAdmin(){
    if(!esAdministrador()){
        header("Location: ../Pagina_inicio/index.php");
        exit();
    }
}
?>