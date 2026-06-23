<?php

$conexion = mysqli_connect(
    "localhost",
    "root",
    "",
    "patronato"
);

if(!$conexion){

    die("Error de conexión");

}