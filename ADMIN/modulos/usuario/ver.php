<?php
require_once("../../config/conexion.php");
$conexion = Connection();
$id = $_GET["id"];
$sql = "
SELECT
u.id_usuario,
u.dni,
u.nombre,
u.apellido,
u.telefono,
u.codigo,
u.fecha_nacimiento,
v.numero_vivienda,
v.cuota,
v.estado,
s.nombre_sector,
se.nombre_servicio
FROM usuarios u
LEFT JOIN viviendas v
ON u.id_usuario = v.id_usuario
LEFT JOIN sectores s
ON v.id_sector = s.id_sector
LEFT JOIN servicios se
ON v.id_servicio = se.id_servicio
WHERE u.id_usuario = ?
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
$datos = $stmt->fetchAll();
if(!$datos){
    exit("Usuario no encontrado");
}
?>
<h3><?= $datos[0]["nombre"] ?> <?= $datos[0]["apellido"] ?></h3>
<hr>
<p><b>DNI:</b> <?= $datos[0]["dni"] ?></p>
<p><b>Teléfono:</b> <?= $datos[0]["telefono"] ?></p>
<p><b>Código:</b> <?= $datos[0]["codigo"] ?></p>
<p><b>Fecha nacimiento:</b> <?= $datos[0]["fecha_nacimiento"] ?></p>
<h4>Viviendas</h4>
<table class="tabla_datos">
<thead>
<tr>
<th>Casa</th>
<th>Sector</th>
<th>Servicio</th>
<th>Cuota</th>
<th>Estado</th>
</tr>
</thead>
<tbody>
<?php foreach($datos as $d): ?>
<tr>
<td><?= $d["numero_vivienda"] ?></td>
<td><?= $d["nombre_sector"] ?></td>
<td><?= $d["nombre_servicio"] ?></td>
<td>L <?= $d["cuota"] ?></td>
<td><?= $d["estado"] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>