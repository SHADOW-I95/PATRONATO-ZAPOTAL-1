<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/pagos.php';
require_once __DIR__ . '/../../ADMIN/modulos/agua/helpers_agua.php';

if (!esUsuarioComun()) {
    header("Location: ../login/login.php");
    exit;
}

$conexion = Connection();

$stmt = $conexion->prepare("SELECT dni, nombre, apellido, telefono, fecha_registro, foto_perfil FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['id']]);
$usuario = $stmt->fetch();

$stmt_viviendas = $conexion->prepare(
    "SELECT v.id_vivienda, v.numero_vivienda, v.cuota, s.nombre_sector, se.nombre_servicio, ep.nombre_estado_pago
     FROM viviendas v
     LEFT JOIN sectores s ON v.id_sector = s.id_sector
     LEFT JOIN servicios se ON v.id_servicio = se.id_servicio
     LEFT JOIN estado_pago ep ON v.id_estado_pago = ep.id_estado_pago
     WHERE v.id_usuario = ?"
);
$stmt_viviendas->execute([$_SESSION['id']]);
$viviendas = $stmt_viviendas->fetchAll();

// Para cada vivienda: sus meses pendientes reales y si ya tiene una
// solicitud de pago "En revisión" (mientras la haya, no se puede subir otra).
$stmt_solicitud_activa = $conexion->prepare(
    "SELECT id_solicitud, codigo_referencia, cantidad_meses, monto_declarado, fecha_solicitud
     FROM solicitudes_pago
     WHERE id_vivienda = ? AND id_estado_solicitud = 1
     ORDER BY fecha_solicitud DESC LIMIT 1"
);
$stmt_ultimo_rechazo = $conexion->prepare(
    "SELECT motivo_rechazo, fecha_revision FROM solicitudes_pago
     WHERE id_vivienda = ? AND id_estado_solicitud = 3
     ORDER BY fecha_revision DESC LIMIT 1"
);

foreach ($viviendas as &$v) {
    $v['meses_pendientes'] = obtener_meses_pendientes($conexion, (int) $v['id_vivienda']);
    $v['cuota'] = obtener_cuota_efectiva($conexion, (int) $v['id_vivienda']); // ya con descuento por edad, si aplica

    $stmt_solicitud_activa->execute([$v['id_vivienda']]);
    $v['solicitud_activa'] = $stmt_solicitud_activa->fetch();

    $v['ultimo_rechazo'] = null;
    if (!$v['solicitud_activa']) {
        $stmt_ultimo_rechazo->execute([$v['id_vivienda']]);
        $v['ultimo_rechazo'] = $stmt_ultimo_rechazo->fetch();
    }
}
unset($v);

$nombres_meses_corto = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];

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
      <div class="perfil-foto" id="perfil-foto-contenedor">
        <?php if (!empty($usuario['foto_perfil'])): ?>
          <img src="../<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil" id="perfil-foto-img">
        <?php else: ?>
          <i class="fa-solid fa-user perfil-foto-placeholder" id="perfil-foto-placeholder"></i>
        <?php endif; ?>
      </div>

      <label for="input-foto-perfil" class="btn-cambiar-foto">
        <i class="fa-solid fa-camera"></i> <?= !empty($usuario['foto_perfil']) ? 'Cambiar foto' : 'Agregar foto' ?>
      </label>
      <input type="file" id="input-foto-perfil" accept="image/*" style="display:none;">
      <p id="mensaje-foto-perfil" class="mensaje-codigo"></p>
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

    <div class="lista-viviendas">
      <?php foreach ($viviendas as $v):
        $totalPendiente = count($v['meses_pendientes']);
        $montoTotal = $totalPendiente * (float) $v['cuota'];
        $tieneSolicitud = (bool) $v['solicitud_activa'];
      ?>
      <div class="vivienda-card">

        <button type="button" class="vivienda-cabecera" onclick="toggleVivienda(<?= (int) $v['id_vivienda'] ?>)">
          <div class="vivienda-cabecera-info">
            <span class="vivienda-numero">Vivienda #<?= htmlspecialchars($v['numero_vivienda']) ?></span>
            <span class="vivienda-sub"><?= htmlspecialchars($v['nombre_sector'] ?? '—') ?> · <?= htmlspecialchars($v['nombre_servicio'] ?? '—') ?></span>
          </div>
          <div class="vivienda-cabecera-derecha">
            <?php if ($tieneSolicitud): ?>
              <span class="badge badge-revision">En revisión</span>
            <?php else: ?>
              <span class="badge <?= clase_badge_perfil($v['nombre_estado_pago']) ?>"><?= htmlspecialchars($v['nombre_estado_pago'] ?? '—') ?></span>
            <?php endif; ?>
            <i class="fa-solid fa-chevron-down flecha-vivienda" id="flecha-<?= (int) $v['id_vivienda'] ?>"></i>
          </div>
        </button>

        <div class="vivienda-detalle" id="detalle-<?= (int) $v['id_vivienda'] ?>">

          <div class="vivienda-datos-grid">
            <div><span>Cuota mensual</span><strong>L<?= number_format($v['cuota'], 2) ?></strong></div>
            <div><span>Meses pendientes</span><strong><?= $totalPendiente ?></strong></div>
            <div><span>Total adeudado</span><strong>L<?= number_format($montoTotal, 2) ?></strong></div>
          </div>

          <?php if ($tieneSolicitud): ?>
            <!-- Ya hay un comprobante subido esperando revisión: no se puede subir otro -->
            <div class="aviso-revision">
              <i class="fa-solid fa-hourglass-half"></i>
              <div>
                <strong>Tu pago está en revisión.</strong>
                <p>
                  Código <?= htmlspecialchars($v['solicitud_activa']['codigo_referencia']) ?> ·
                  <?= (int) $v['solicitud_activa']['cantidad_meses'] ?> mes(es) ·
                  L<?= number_format($v['solicitud_activa']['monto_declarado'], 2) ?><br>
                  Enviado el <?= date('d/m/Y', strtotime($v['solicitud_activa']['fecha_solicitud'])) ?>. El patronato confirmará el pago en cuanto lo verifique en el banco.
                </p>
              </div>
            </div>

          <?php elseif ($totalPendiente === 0): ?>
            <p class="al-dia">✅ Esta vivienda está al día, no debe ningún mes.</p>

          <?php else: ?>

            <?php if ($v['ultimo_rechazo']): ?>
            <div class="aviso-rechazo">
              <i class="fa-solid fa-circle-exclamation"></i>
              <div>
                <strong>Tu último comprobante fue rechazado.</strong>
                <p><?= htmlspecialchars($v['ultimo_rechazo']['motivo_rechazo'] ?: 'No se especificó un motivo.') ?></p>
              </div>
            </div>
            <?php endif; ?>

            <?php
              // El código se genera al cargar la página, para que el usuario
              // lo tenga ANTES de ir al banco a depositar (lo necesita para
              // escribirlo en el concepto de la transferencia).
              $codigoReferencia = 'PZ-' . $v['id_vivienda'] . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            ?>
            <form class="form-pago" data-id-vivienda="<?= (int) $v['id_vivienda'] ?>" data-cuota="<?= (float) $v['cuota'] ?>">
              <input type="hidden" name="codigo_referencia" value="<?= htmlspecialchars($codigoReferencia) ?>">

              <label>¿Cuántos meses vas a pagar?</label>
              <select name="cantidad_meses" class="select-meses" required>
                <?php for ($i = 1; $i <= $totalPendiente; $i++): ?>
                <option value="<?= $i ?>">
                  <?= $i ?> mes<?= $i > 1 ? 'es' : '' ?>
                  (<?= htmlspecialchars($nombres_meses_corto[(int) $v['meses_pendientes'][0]['mes']]) ?> <?= $v['meses_pendientes'][0]['anio'] ?>
                  <?php if ($i > 1): ?> - <?= htmlspecialchars($nombres_meses_corto[(int) $v['meses_pendientes'][$i-1]['mes']]) ?> <?= $v['meses_pendientes'][$i-1]['anio'] ?><?php endif; ?>)
                </option>
                <?php endfor; ?>
              </select>

              <div class="monto-a-pagar">
                Monto a depositar: <strong class="monto-valor">L<?= number_format($v['cuota'], 2) ?></strong>
              </div>

              <div class="datos-bancarios">
                <h4><i class="fa-solid fa-building-columns"></i> Datos para el depósito</h4>
                <p><span>Banco</span> <?= htmlspecialchars(BANCO_NOMBRE) ?></p>
                <p><span>Cuenta</span> <?= htmlspecialchars(BANCO_NUMERO_CUENTA) ?></p>
                <p><span>A nombre de</span> <?= htmlspecialchars(BANCO_TITULAR) ?></p>
                <p class="nota-referencia">
                  Importante: escribe el código
                  <strong class="codigo-referencia-valor"><?= htmlspecialchars($codigoReferencia) ?></strong>
                  en el concepto/nota de la transferencia — así el patronato encuentra tu depósito más rápido.
                </p>
              </div>

              <label>Foto o captura del comprobante de depósito</label>
              <input type="file" name="comprobante" accept="image/*,application/pdf" required>

              <button type="submit" class="btn-subir-comprobante">
                <i class="fa-solid fa-upload"></i> Subir comprobante
              </button>

              <p class="mensaje-pago"></p>
            </form>

          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

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

<script>
/* ===== Foto de perfil ===== */
const inputFotoPerfil = document.getElementById('input-foto-perfil');

if (inputFotoPerfil) {
    inputFotoPerfil.addEventListener('change', () => {
        const archivo = inputFotoPerfil.files[0];
        const mensaje = document.getElementById('mensaje-foto-perfil');
        mensaje.className = 'mensaje-codigo';
        mensaje.textContent = '';

        if (!archivo) return;

        if (archivo.size > 5 * 1024 * 1024) {
            mensaje.textContent = 'La imagen es muy pesada (máximo 5MB).';
            mensaje.classList.add('mensaje-error');
            return;
        }

        const datos = new FormData();
        datos.append('foto', archivo);

        mensaje.textContent = 'Subiendo...';

        fetch('subir_foto_perfil.php', { method: 'POST', body: datos })
            .then((r) => r.json())
            .then((resp) => {
                const textos = {
                    sin_permiso: 'Tu sesión expiró, vuelve a iniciar sesión.',
                    archivo_invalido: 'El archivo debe ser una imagen (jpg, png o webp).',
                    archivo_muy_grande: 'La imagen es muy pesada (máximo 5MB).',
                    error_guardando: 'Ocurrió un error al guardar. Intenta de nuevo.',
                };

                if (resp.ok) {
                    mensaje.textContent = '';
                    const contenedor = document.getElementById('perfil-foto-contenedor');
                    contenedor.innerHTML = `<img src="${resp.ruta}?t=${Date.now()}" alt="Foto de perfil" id="perfil-foto-img">`;
                    document.querySelector('.btn-cambiar-foto').innerHTML = '<i class="fa-solid fa-camera"></i> Cambiar foto';
                } else {
                    mensaje.textContent = textos[resp.error] || 'No se pudo subir la foto.';
                    mensaje.classList.add('mensaje-error');
                }
            })
            .catch(() => {
                mensaje.textContent = 'Error de conexión. Intenta de nuevo.';
                mensaje.classList.add('mensaje-error');
            });
    });
}
</script>

<script>
/* ===== Acordeón de viviendas + flujo de pago ===== */

function toggleVivienda(id) {
  const detalle = document.getElementById('detalle-' + id);
  const flecha = document.getElementById('flecha-' + id);
  const abierta = detalle.classList.toggle('abierta');
  flecha.classList.toggle('flecha-abierta', abierta);
}

// Actualiza el monto a pagar cuando cambian la cantidad de meses
document.querySelectorAll('.form-pago').forEach((form) => {
  const cuota = parseFloat(form.dataset.cuota);
  const select = form.querySelector('.select-meses');
  const montoValor = form.querySelector('.monto-valor');

  select.addEventListener('change', () => {
    const total = cuota * parseInt(select.value, 10);
    montoValor.textContent = 'L' + total.toFixed(2);
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const mensaje = form.querySelector('.mensaje-pago');
    const boton = form.querySelector('.btn-subir-comprobante');
    mensaje.className = 'mensaje-pago';
    mensaje.textContent = '';

    const archivo = form.querySelector('input[name="comprobante"]').files[0];
    if (!archivo) return;

    // Límite de tamaño en el navegador para no esperar una subida larga
    // que el servidor va a rechazar de todas formas.
    if (archivo.size > 8 * 1024 * 1024) {
      mensaje.textContent = 'El archivo es muy pesado (máximo 8MB).';
      mensaje.classList.add('mensaje-error-pago');
      return;
    }

    const datos = new FormData(form);
    datos.append('id_vivienda', form.dataset.idVivienda);

    boton.disabled = true;
    boton.textContent = 'Subiendo...';

    fetch('subir_pago.php', { method: 'POST', body: datos })
      .then((r) => r.json())
      .then((resp) => {
        const textos = {
          sin_permiso: 'Tu sesión expiró, vuelve a iniciar sesión.',
          vivienda_no_valida: 'Esa vivienda no te pertenece.',
          ya_tiene_solicitud: 'Ya hay un comprobante de esta vivienda en revisión.',
          archivo_invalido: 'El archivo debe ser una imagen o un PDF.',
          archivo_muy_grande: 'El archivo es muy pesado (máximo 8MB).',
          datos_incompletos: 'Faltan datos, intenta de nuevo.',
          error_guardando: 'Ocurrió un error al guardar. Intenta de nuevo.',
        };

        if (resp.ok) {
          mensaje.textContent = 'Comprobante enviado. Quedó en revisión.';
          mensaje.classList.add('mensaje-exito-pago');
          setTimeout(() => location.reload(), 1200);
        } else {
          mensaje.textContent = textos[resp.error] || 'No se pudo subir el comprobante.';
          mensaje.classList.add('mensaje-error-pago');
          boton.disabled = false;
          boton.innerHTML = '<i class="fa-solid fa-upload"></i> Subir comprobante';
        }
      })
      .catch(() => {
        mensaje.textContent = 'Error de conexión. Intenta de nuevo.';
        mensaje.classList.add('mensaje-error-pago');
        boton.disabled = false;
        boton.innerHTML = '<i class="fa-solid fa-upload"></i> Subir comprobante';
      });
  });
});
</script>

</body>
</html>