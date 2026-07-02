
<?php
  include('conexion.php');
 

  $id_usuario = null;
  $dni = $_POST['dni'];
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $fecha_nac = $_POST['fecha_nac'];
  $telefono = $_POST['telefono'];
  $email = $_POST['email'];  
  $sector = $_POST['sector'];
  $numero_casa = $_POST['numero_casa'];
  $tipo_servicio = $_POST['tipo_servicio'];
  $cant_propiedades = $_POST['cant_propiedades'];
  $estado = $_POST['estado'];
  $observaciones = $_POST['observaciones'];

  $sql = "INSERT INTO usuarios
  (id_usuario,DNI,NOMBRE,APELLIDO,FECHA_NACIMIENTO,TELEFONO,CORREO,SECTOR,NUMERO_CASA,TIPO_SERVICIO,CANT_PROPIEDADES,ESTADO,OBSERVACIONES)
  VALUES
  ('$id_usuario','$dni','$nombre','$apellido','$fecha_nac','$telefono','$email','$sector','$numero_casa',
  '$tipo_servicio','$cant_propiedades','$estado','$observaciones')";

  $query = mysqli_query($conexion,$sql);

  if ($query) {
    header('location: usuario.php');
  }

?>

