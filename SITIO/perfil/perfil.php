<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';

if (!esUsuarioComun()) {
    header("Location: ../login/login.php");
    exit;
}

$conexion = Connection();

$stmt = $conexion->prepare("SELECT dni, nombre, apellido, telefono, fecha_registro FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['id']]);
$usuario = $stmt->fetch();

$stmt_viviendas = $conexion->prepare(
    "SELECT v.numero_vivienda, v.cuota, s.nombre_sector, se.nombre_servicio, ep.nombre_estado_pago
     FROM viviendas v
     LEFT JOIN sectores s ON v.id_sector = s.id_sector
     LEFT JOIN servicios se ON v.id_servicio = se.id_servicio
     LEFT JOIN estado_pago ep ON v.id_estado_pago = ep.id_estado_pago
     WHERE v.id_usuario = ?"
);
$stmt_viviendas->execute([$_SESSION['id']]);
$viviendas = $stmt_viviendas->fetchAll();

function clase_badge_perfil($nombre_estado)
{
    if ($nombre_estado === 'Pagado') return 'badge-pagado';
    if ($nombre_estado === 'Mora')   return 'badge-mora';
    return 'badge-pendiente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Perfil - Patronato de Agua</title>
<link rel="stylesheet" href="perfil.css">
</head>
<body>

<div class="sidebar">
  <div class="sidebar-logo">
    <i class="fa-solid fa-droplet"></i>
    <span>PATRONATO<br> El zapotal</span>
  </div>

  <p class="sidebar-titulo">MENU PRINCIPAL</p>

  <a href="../index.php"><i class="fa-solid fa-house"></i> Inicio</a>
  <a href="perfil.php" class="activo"><i class="fa-solid fa-user"></i> Mi Perfil</a>
  <a href="../index.php#section4"><i class="fa-solid fa-triangle-exclamation"></i> Reportar problema</a>
  <a href="login/cerrar_sesion.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
</div>

<div class="contenido">

  <div class="topbar">
    <div class="topbar-derecha">
      <div class="topbar-usuario">
        <div class="topbar-usuario-texto">
          <span class="nombre"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></span>
          <span class="abonado">DNI: <?= htmlspecialchars($usuario['dni']) ?></span>
        </div>
      </div>
    </div>
  </div>

  <div class="perfil-header">
    <div class="perfil-header-icon">
      <i class="fa-solid fa-user"></i>
    </div>
    <div>
      <h1>Mi Perfil</h1>
      <p>Consulta tu información personal y tus viviendas registradas.</p>
    </div>
  </div>

  <div class="perfil-card">

    <div class="perfil-foto-col">
      <div class="perfil-foto">
        <i class="fa-solid fa-user perfil-foto-placeholder"></i>
      </div>
    </div>

    <div class="perfil-info-col">
      <h2><i class="fa-solid fa-address-card"></i> Información Personal</h2>

      <div class="perfil-dato">
        <span class="perfil-label">Nombre completo:</span>
        <span class="perfil-valor"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></span>
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">DNI:</span>
        <span class="perfil-valor"><?= htmlspecialchars($usuario['dni']) ?></span>
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">Teléfono:</span>
        <span class="perfil-valor"><?= htmlspecialchars($usuario['telefono'] ?? '—') ?></span>
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">Usuario desde:</span>
        <span class="perfil-valor"><?= htmlspecialchars($usuario['fecha_registro']) ?></span>
      </div>
    </div>
  </div>

  <div class="seccion">
    <h3>Mis viviendas</h3>

    <?php if (empty($viviendas)): ?>
      <p>No tienes viviendas registradas todavía.</p>
    <?php else: ?>
    <table class="tabla_datos">
      <thead>
        <tr>
          <th>Vivienda</th>
          <th>Sector</th>
          <th>Servicio</th>
          <th>Cuota (L)</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($viviendas as $v): ?>
        <tr>
          <td>#<?= htmlspecialchars($v['numero_vivienda']) ?></td>
          <td><?= htmlspecialchars($v['nombre_sector'] ?? '—') ?></td>
          <td><?= htmlspecialchars($v['nombre_servicio'] ?? '—') ?></td>
          <td>L<?= number_format($v['cuota'], 2) ?></td>
          <td><span class="badge <?= clase_badge_perfil($v['nombre_estado_pago']) ?>"><?= htmlspecialchars($v['nombre_estado_pago'] ?? '—') ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

</div>

</body>
</html>