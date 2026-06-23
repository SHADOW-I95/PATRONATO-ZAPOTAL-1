<?php
include('../conexion.php');

$sql = "SELECT * FROM usuarios";
$resultado = mysqli_query($conexion,$sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../assets/css/usuarios.css">
<link rel="stylesheet" href="../assets/css/barra_lateral.css">
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/barra_superior.css">
<title>Usuarios</title>
</head>
<body>

<?php 
include '../layout/barra_superior.php';
?>


<div class="contenido">

    <div class="encabezado">

        <h1>Usuarios</h1>

        <a href="agregar.php" class="btn-nuevo">
            + Nuevo Usuario
        </a>

    </div>

    <div class="buscador">

        <input
        type="text"
        placeholder="Buscar usuario...">

    </div>

    <div class="tabla-contenedor">

        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>DNI</th>
                    <th>NOMBRE COMPLETO</th>
                    <th>SECTOR</th>
                    <th>TELEFONO</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>

                </tr>

            </thead>

            <tbody>

                <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

                <tr>

                    <td><?= $fila['DNI']; ?></td>

                    <td><?= $fila['NOMBRE']; ?></td>

                    <td><?= $fila['APELLIDO']; ?></td>

                    <td><?= $fila['SECTOR']; ?></td>

                    <td>L <?= $fila['CUOTA_MENSUAL']; ?></td>

                    <td>

                        <span class="estado <?= strtolower($fila['ESTADO']) ?>">
                            <?= $fila['ESTADO']; ?>
                        </span>

                    </td>

                    <td>

                        <a href="ver.php?id=<?= $fila['id_usuario']; ?>">👁</a>

                        <a href="editar.php?id=<?= $fila['id_usuario']; ?>">✏</a>

                        <a href="eliminar.php?id=<?= $fila['id_usuario']; ?>">🗑</a>

                    </td>

                </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<?php
include '../layout/barra_lateral.php'; 

 ?>

</body>
</html>