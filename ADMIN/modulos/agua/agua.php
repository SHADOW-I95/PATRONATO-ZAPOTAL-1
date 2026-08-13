<?php
require_once __DIR__ . "/../../config/conexion.php";
require_once __DIR__ . "/helpers_agua.php";
$conexion = Connection();

// 1. Usuarios para el select del formulario
$sql_usuarios = "SELECT id_usuario, CONCAT(nombre, ' ', apellido) AS nombre_completo, dni
                  FROM usuarios ORDER BY nombre";
$stmt_usuarios = $conexion->prepare($sql_usuarios);
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->fetchAll();

// 2. Si ya eligieron un usuario, traemos sus viviendas con su estado actual
$id_usuario_seleccionado = filter_input(INPUT_GET, "id_usuario", FILTER_VALIDATE_INT);
$viviendas = [];

if ($id_usuario_seleccionado) {
    $sql_viviendas = "SELECT v.id_vivienda, v.numero_vivienda, v.cuota, s.nombre_sector
                       FROM viviendas v
                       LEFT JOIN sectores s ON v.id_sector = s.id_sector
                       WHERE v.id_usuario = ?";
    $stmt_viviendas = $conexion->prepare($sql_viviendas);
    $stmt_viviendas->execute([$id_usuario_seleccionado]);
    $viviendas = $stmt_viviendas->fetchAll();

    foreach ($viviendas as &$v) {
        $v['estado'] = refrescar_estado_vivienda($conexion, (int) $v['id_vivienda']);
    }
    unset($v);
}

// 3. Estado actual de todas las viviendas (Pagado / Pendiente / Mora)
$sql_estado = "SELECT v.id_vivienda, v.numero_vivienda, v.cuota, s.nombre_sector,
                      CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario, u.dni
               FROM viviendas v
               LEFT JOIN sectores s ON v.id_sector = s.id_sector
               LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
               ORDER BY u.nombre, v.numero_vivienda";
$stmt_estado = $conexion->prepare($sql_estado);
$stmt_estado->execute();
$todas_viviendas = $stmt_estado->fetchAll();

foreach ($todas_viviendas as &$viv) {
    $viv['estado'] = refrescar_estado_vivienda($conexion, (int) $viv['id_vivienda']);
}
unset($viv);

// 4. Historial de pagos ya registrados
$sql_historial = "SELECT pa.id_pago_agua, pa.numero_recibo, pa.fecha_pago_agua, pa.total,
                         v.numero_vivienda, s.nombre_sector,
                         CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario, u.dni
                  FROM pagos_agua pa
                  INNER JOIN viviendas v ON pa.id_vivienda = v.id_vivienda
                  LEFT JOIN sectores s ON v.id_sector = s.id_sector
                  INNER JOIN usuarios u ON pa.id_usuario = u.id_usuario
                  ORDER BY pa.fecha_pago_agua DESC, pa.id_pago_agua DESC";
$stmt_historial = $conexion->prepare($sql_historial);
$stmt_historial->execute();
$historial = $stmt_historial->fetchAll();

// 5. Detalle de meses pagados por cada registro (para el botón "Ver detalle")
$detalle_por_pago = [];
if ($historial) {
    $ids = array_column($historial, 'id_pago_agua');
    $in  = implode(',', array_fill(0, count($ids), '?'));

    $sql_detalle = "SELECT id_pago_agua, anio, mes, monto FROM detalle_pago_agua
                     WHERE id_pago_agua IN ($in) ORDER BY id_pago_agua, anio, mes";
    $stmt_detalle = $conexion->prepare($sql_detalle);
    $stmt_detalle->execute($ids);

    foreach ($stmt_detalle->fetchAll() as $fila) {
        $detalle_por_pago[$fila['id_pago_agua']][] = $fila;
    }
}

$nombres_meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                   7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];

// Recibos recién generados (vienen en la URL tras guardar un pago), para mostrar "Imprimir factura"
$recibos_nuevos = [];
if (!empty($_GET['ok']) && !empty($_GET['recibos'])) {
    $recibos_nuevos = array_map('intval', explode(',', $_GET['recibos']));
}

function clase_badge($nombre_estado)
{
    if ($nombre_estado === 'Pagado') return 'badge-pagado';
    if ($nombre_estado === 'Mora')   return 'badge-mora';
    return 'badge-pendiente';
}
?>

<div class="modulo_header">
    <div class="encabezado">
        <h1>Historial de Pagos</h1>
    </div>
    <div class="opciones">
        <input type="text" placeholder="Nombre o DNI..." class="buscar">

        <!-- Filtra la tabla de "Estado de viviendas" por Pagado/Pendiente/Mora -->
        <select id="filtro-estado" class="filtro-estado">
            <option value="">Todos los estados</option>
            <option value="Pagado">Pagado</option>
            <option value="Pendiente">Pendiente</option>
            <option value="Mora">Mora</option>
        </select>

        <button class="btn_nuevo" id="abrir-modal">+ Registrar pago</button>
    </div>
</div>

<!-- Aviso de pago guardado, con enlaces para imprimir la factura de cada vivienda pagada -->
<?php if ($recibos_nuevos): ?>
<div class="aviso-exito">
    <span>Pago registrado correctamente.</span>
    <?php foreach ($recibos_nuevos as $recibo): ?>
    <a href="modulos/agua/factura.php?recibo=<?= $recibo ?>" target="_blank" class="btn-factura">
        Imprimir factura #<?= sprintf('%06d', $recibo) ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Estado de viviendas -->
<div class="seccion">
    <h3>Estado de viviendas</h3>
    <table class="tabla_datos">
        <thead>
            <tr>
                <th>#</th>
                <th>Vivienda</th>
                <th>Usuario</th>
                <th>Sector</th>
                <th>Cuota (L)</th>
                <th>Último mes pagado</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($todas_viviendas as $i => $viv): ?>
            <!-- data-estado: usado por agua.js para filtrar con el selector de arriba -->
            <tr data-estado="<?= $viv['estado']['nombre'] ?>">
                <td><?= $i + 1 ?></td>
                <td>#<?= htmlspecialchars($viv['numero_vivienda']) ?></td>
                <td><?= htmlspecialchars($viv['nombre_usuario'] ?? '—') ?></td>
                <td><?= htmlspecialchars($viv['nombre_sector'] ?? '—') ?></td>
                <td class="col-monto">L<?= number_format($viv['cuota'], 2) ?></td>
                <td>
                    <?php if ($viv['estado']['ultimo_pago']): ?>
                    <?= $nombres_meses[$viv['estado']['ultimo_pago']['mes']] ?>
                    <?= $viv['estado']['ultimo_pago']['anio'] ?>
                    <?php else: ?>
                    Sin pagos
                    <?php endif; ?>
                </td>
                <td><span
                        class="badge <?= clase_badge($viv['estado']['nombre']) ?>"><?= $viv['estado']['nombre'] ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Historial de pagos -->
<div class="seccion">
    <h3>Pagos registrados</h3>
    <table class="tabla_datos">
        <thead>
            <tr>
                <th>#</th>
                <th>Recibo</th>
                <th>Usuario</th>
                <th>Vivienda</th>
                <th>Sector</th>
                <th>Fecha</th>
                <th>Total (L)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historial as $i => $pago): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $pago['numero_recibo'] ?></td>
                <td><?= htmlspecialchars($pago['nombre_usuario']) ?> (<?= htmlspecialchars($pago['dni']) ?>)</td>
                <td>#<?= htmlspecialchars($pago['numero_vivienda']) ?></td>
                <td><?= htmlspecialchars($pago['nombre_sector'] ?? '—') ?></td>
                <td><?= $pago['fecha_pago_agua'] ?></td>
                <td class="col-monto">L<?= number_format($pago['total'], 2) ?></td>
                <td class="col-acciones">
                    <button type="button" class="btn-detalle" data-recibo="<?= $pago['numero_recibo'] ?>"
                        data-detalle='<?= htmlspecialchars(json_encode($detalle_por_pago[$pago['id_pago_agua']] ?? []), ENT_QUOTES) ?>'>
                        Ver detalle
                    </button>
                    <a class="btn-factura" href="modulos/agua/factura.php?recibo=<?= $pago['numero_recibo'] ?>"
                        target="_blank">
                        Imprimir factura
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Registrar pago -->
<div class="modal" id="modal">
    <div class="modal-contenido">
        <span class="cerrar" data-cerrar-modal>✕</span>
        <div class="formulario">
            <h4>+ Registrar pago</h4>

            <form method="GET">
                <input type="hidden" name="modulo" value="agua">
                <div class="campo">
                    <label>Usuario</label>
                    <select name="id_usuario" required>
                        <option value="">Selecciona…</option>
                        <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id_usuario'] ?>"
                            <?= $id_usuario_seleccionado == $u['id_usuario'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre_completo']) ?> (<?= htmlspecialchars($u['dni']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-secundario">Buscar viviendas</button>
            </form>

            <?php if ($id_usuario_seleccionado && $viviendas): ?>
            <form action="modulos/agua/registro_pago.php" method="POST">
                <input type="hidden" name="id_usuario" value="<?= $id_usuario_seleccionado ?>">

                <div class="lista-viviendas">
                    <?php foreach ($viviendas as $v): ?>
                    <div class="tarjeta-vivienda">
                        <div class="tarjeta-vivienda-header">
                            <label>
                                <input type="checkbox" name="pagos[<?= $v['id_vivienda'] ?>][aplicar]" value="1">
                                <strong>#<?= htmlspecialchars($v['numero_vivienda']) ?></strong>
                                — <?= htmlspecialchars($v['nombre_sector'] ?? 'Sin sector') ?>
                            </label>
                            <span
                                class="badge <?= clase_badge($v['estado']['nombre']) ?>"><?= $v['estado']['nombre'] ?></span>
                        </div>
                        <div class="vivienda">
                            <div class="campo">
                                <label>Meses a pagar</label>
                                <input type="number" min="1" max="24" value="1"
                                    name="pagos[<?= $v['id_vivienda'] ?>][meses]" class="input-meses">
                            </div>
                            <div class="campo">
                                <label>Mes inicial</label>
                                <input type="month" name="pagos[<?= $v['id_vivienda'] ?>][mes_inicial]"
                                    class="input-mes-inicial" value="<?= date('Y-m') ?>">
                            </div>
                            <div class="campo">
                                <label>Monto por mes (L)</label>
                                <input type="number" step="0.01" min="0"
                                    name="pagos[<?= $v['id_vivienda'] ?>][monto_mensual]" class="input-monto-mensual"
                                    value="<?= htmlspecialchars($v['cuota']) ?>">
                            </div>
                            <div class="campo">
                                <label>Total a pagar (L)</label>
                                <input type="text" class="input-total" value="0.00" readonly>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="campo">
                    <label>Método de pago</label>
                    <select name="metodo_pago">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Depósito">Depósito</option>
                    </select>
                </div>

                <div class="campo">
                    <label>Observaciones (opcional)</label>
                    <input type="text" name="observaciones">
                </div>

                <div class="form-acciones">
                    <button type="submit" class="btn-primario">Guardar Pago</button>
                </div>
            </form>
            <?php elseif ($id_usuario_seleccionado): ?>
            <p>Este usuario no tiene viviendas registradas.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Detalle de pago -->
<div class="modal" id="modal-detalle">
    <div class="modal-contenido" style="width: 420px;">
        <span class="cerrar" data-cerrar-modal>✕</span>
        <div class="formulario">
            <h4>Detalle del pago <span id="detalle-recibo"></span></h4>
            <table class="tabla_datos">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Año</th>
                        <th>Monto (L)</th>
                    </tr>
                </thead>
                <tbody id="detalle-cuerpo"></tbody>
            </table>
        </div>
    </div>
</div>