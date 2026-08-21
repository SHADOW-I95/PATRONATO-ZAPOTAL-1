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
<title>Mi Perfil - Patronato el Zapotal</title>
<link rel="stylesheet" href="../assets/css/variables.css">
<link rel="stylesheet" href="perfil.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="overlay-sidebar" id="OVERLAY_SIDEBAR" onclick="cerrarSidebar()"></div>

<div class="sidebar" id="SIDEBAR">
  <div class="sidebar-logo">
    <i class="fa-solid fa-droplet"></i>
    <span>PATRONATO<br> El Zapotal</span>
  </div>

  <p class="sidebar-titulo">MENU PRINCIPAL</p>

  <a href="../index.php"><i class="fa-solid fa-house"></i> Inicio</a>
  <a href="perfil.php" class="activo"><i class="fa-solid fa-user"></i> Mi Perfil</a>
  <a href="../index.php#section4"><i class="fa-solid fa-triangle-exclamation"></i> Reportar problema</a>
  <a href="../login/cerrar_sesion.php"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
</div>

<div class="contenido">

  <div class="topbar">
    <button class="btn-menu-movil" id="BTN_MENU_MOVIL" onclick="abrirSidebar()" aria-label="Abrir menú">
      <i class="fa-solid fa-bars"></i>
    </button>
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

  <div class="seccion">
    <h3><i class="fa-solid fa-key"></i> Cambiar mi código de acceso</h3>
    <p class="seccion-ayuda">
      Este es el código que usas junto a tu nombre y DNI para iniciar sesión.
      Puedes cambiarlo por uno que te sea más fácil de recordar.
    </p>

    <form id="form_cambiar_codigo" class="form-codigo">
      <div class="campo-codigo">
        <label for="codigo_actual">Código actual</label>
        <input type="text" id="codigo_actual" name="codigo_actual" required autocomplete="off">
      </div>
      <div class="campo-codigo">
        <label for="codigo_nuevo">Código nuevo</label>
        <input type="text" id="codigo_nuevo" name="codigo_nuevo" required minlength="4" maxlength="50" autocomplete="off">
      </div>
      <div class="campo-codigo">
        <label for="codigo_nuevo_confirmar">Confirmar código nuevo</label>
        <input type="text" id="codigo_nuevo_confirmar" name="codigo_nuevo_confirmar" required minlength="4" maxlength="50" autocomplete="off">
      </div>

      <button type="submit" class="btn-guardar-codigo">
        <i class="fa-solid fa-floppy-disk"></i> Guardar código nuevo
      </button>

      <p id="mensaje_codigo" class="mensaje-codigo"></p>
    </form>
  </div>

</div>

<script>
document.getElementById('form_cambiar_codigo').addEventListener('submit', function (e) {
  e.preventDefault();

  const mensaje = document.getElementById('mensaje_codigo');
  mensaje.className = 'mensaje-codigo';
  mensaje.textContent = '';

  const nuevo = document.getElementById('codigo_nuevo').value.trim();
  const confirmar = document.getElementById('codigo_nuevo_confirmar').value.trim();

  if (nuevo !== confirmar) {
    mensaje.textContent = 'El código nuevo y la confirmación no coinciden.';
    mensaje.classList.add('mensaje-error');
    return;
  }

  fetch('cambiar_codigo.php', {
    method: 'POST',
    body: new FormData(this)
  })
    .then(r => r.json())
    .then(datos => {
      const textos = {
        codigo_actual_incorrecto: 'El código actual no es correcto.',
        codigo_muy_corto: 'El código nuevo debe tener al menos 4 caracteres.',
        codigo_duplicado: 'Ese código ya lo está usando otra persona, elige otro.',
        sesion_invalida: 'Tu sesión expiró, vuelve a iniciar sesión.',
        error_guardando: 'Ocurrió un error al guardar. Intenta de nuevo.'
      };

      if (datos.ok) {
        mensaje.textContent = 'Tu código se actualizó correctamente.';
        mensaje.classList.add('mensaje-exito');
        this.reset();
      } else {
        mensaje.textContent = textos[datos.error] || 'No se pudo guardar el código.';
        mensaje.classList.add('mensaje-error');
      }
    })
    .catch(() => {
      mensaje.textContent = 'Error de conexión, intenta de nuevo.';
      mensaje.classList.add('mensaje-error');
    });
});
</script>

<script>
function abrirSidebar() {
  document.getElementById('SIDEBAR').classList.add('abierto');
  document.getElementById('OVERLAY_SIDEBAR').classList.add('visible');
}
function cerrarSidebar() {
  document.getElementById('SIDEBAR').classList.remove('abierto');
  document.getElementById('OVERLAY_SIDEBAR').classList.remove('visible');
}
</script>

</body>
</html>