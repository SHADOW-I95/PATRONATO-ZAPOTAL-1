<?php

function connection() { /* Función para establecer la conexión a la base de datos */
    $host = "localhost"; /* Nombre del host de la base de datos */
    $user = "root"; /* Nombre de usuario de la base de datos */
    $pass = ""; /* Contraseña de la base de datos */
    $bd = "patronato"; /* Nombre de la base de datos */

    $connect = mysqli_connect($host, $user, $pass, $bd); /* Establece la conexión a la base de datos utilizando los parámetros proporcionados */

    if (!$connect) { /* Verifica si la conexión fue exitosa */
        die("Error de conexión: " . mysqli_connect_error()); /* Si la conexión falla, muestra un mensaje de error y termina la ejecución del script */
    }

    return $connect; /* Devuelve el objeto de conexión a la base de datos */
}