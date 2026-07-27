<?php

require_once("../../config/conexion.php");
$conexion = connection();


$id=$_GET['id'];



$sql_editar="
SELECT *
FROM usuarios   
WHERE id_usuario=?
";
$stmt=$conexion->prepare($sql_editar);
$stmt->execute([$id]);
$usuario=$stmt->fetch(PDO::FETCH_ASSOC);



?>


<h2>Editar Usuario</h2>


<form action="actualizar.php" method="POST">


<input type="hidden" 
name="id_usuario"
value="<?= $usuario['id_usuario'] ?>">



<label>DNI</label>

<input 
name="dni"
value="<?= $usuario['dni'] ?>">



<label>Nombre</label>

<input 
name="nombre"
value="<?= $usuario['nombre'] ?>">



<label>Apellido</label>

<input 
name="apellido"
value="<?= $usuario['apellido'] ?>">



<label>Teléfono</label>

<input 
name="telefono"
value="<?= $usuario['telefono'] ?>">

<button>
Guardar cambios
</button>


</form>