<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
requerirPermiso('gastos');
$conexion = Connection();

$categorias = ['Materiales', 'Mantenimiento', 'Pago a empleados', 'Combustible', 'Otro'];

$mes_filtro      = $_GET['mes'] ?? date('Y-m'); // formato YYYY-MM
$categoria_filtro = $_GET['categoria'] ?? '';

$where = ["DATE_FORMAT(fecha_gasto, '%Y-%m') = ?"];
$params = [$mes_filtro];

if ($categoria_filtro !== '') {
    $where[] = "categoria = ?";
    $params[] = $categoria_filtro;
}

$sqlWhere = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT g.id_gasto, g.concepto, g.categoria, g.monto, g.fecha_gasto, g.comprobante_path,
               CONCAT(e.nombre, ' ', e.apellido) AS nombre_empleado
        FROM gastos g
        INNER JOIN empleados e ON g.id_empleado_registro = e.id_empleado
        $sqlWhere
        ORDER BY g.fecha_gasto DESC, g.id_gasto DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$gastos = $stmt->fetchAll();

$total_gastado = array_sum(array_column($gastos, 'monto'));

// Total recaudado del mismo mes, para comparar de un vistazo
$stmtRecaudado = $conexion->prepare(
    "SELECT COALESCE(SUM(total), 0) FROM pagos_agua WHERE DATE_FORMAT(fecha_pago_agua, '%Y-%m') = ?"
);
$stmtRecaudado->execute([$mes_filtro]);
$total_recaudado = (float) $stmtRecaudado->fetchColumn();

$balance = $total_recaudado - $total_gastado;
?>

<div class="modulo_header">
    <div class="encabezado"><h1>Gastos</h1></div>
</div>

<?php if (($_GET['mensaje'] ?? '') === 'guardado'): ?>
<div class="aviso-exito">Gasto registrado correctamente.</div>
<?php endif; ?>

<div class="cards" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 20px;">
    <div class="card card-pagadas"><h3>L<?= number_format($total_recaudado, 2) ?></h3><p>Recaudado este mes</p></div>
    <div class="card card-mora"><h3>L<?= number_format($total_gastado, 2) ?></h3><p>Gastado este mes</p></div>
    <div class="card <?= $balance >= 0 ? 'card-pagadas' : 'card-mora' ?>"><h3>L<?= number_format($balance, 2) ?></h3><p>Balance del mes</p></div>
</div>

<div class="seccion">
    <form method="GET" class="opciones">
        <input type="hidden" name="modulo" value="gastos">
        <input type="month" name="mes" value="<?= htmlspecialchars($mes_filtro) ?>">
        <select name="categoria">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria_filtro === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-secundario">Filtrar</button>
        <button type="button" class="btn_nuevo" id="abrir-modal">+ Nuevo gasto</button>
    </form>
</div>

<div class="seccion">
    <table class="tabla_datos">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Categoría</th>
                <th>Monto</th>
                <th>Registró</th>
                <th>Comprobante</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gastos as $g): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($g['fecha_gasto'])) ?></td>
                <td><?= htmlspecialchars($g['concepto']) ?></td>
                <td><?= htmlspecialchars($g['categoria']) ?></td>
                <td class="col-monto">L<?= number_format($g['monto'], 2) ?></td>
                <td><?= htmlspecialchars($g['nombre_empleado']) ?></td>
                <td>
                    <?php if ($g['comprobante_path']): ?>
                    <a href="../SITIO/<?= htmlspecialchars($g['comprobante_path']) ?>" target="_blank">Ver</a>
                    <?php else: ?>
                    —
                    <?php endif; ?>
                </td>
                <td>
                    <a class="btn-eliminar" href="modulos/gastos/eliminar.php?id=<?= $g['id_gasto'] ?>"
                        onclick="return confirm('¿Eliminar este gasto?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$gastos): ?>
            <tr><td colspan="7">No hay gastos registrados con estos filtros.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Nuevo gasto -->
<div class="modal" id="modal">
    <div class="modal-contenido">
        <span class="cerrar" data-cerrar-modal>✕</span>
        <form class="formulario" action="modulos/gastos/agregar.php" method="POST" enctype="multipart/form-data">
            <h4>+ Nuevo gasto</h4>

            <div class="informacion">
                <div class="campo">
                    <label>Concepto</label>
                    <input type="text" name="concepto" required maxlength="150" placeholder="Ej. Compra de tubería PVC">
                </div>
                <div class="campo">
                    <label>Categoría</label>
                    <select name="categoria" required>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label>Monto (L)</label>
                    <input type="number" name="monto" step="0.01" min="0" required>
                </div>
                <div class="campo">
                    <label>Fecha del gasto</label>
                    <input type="date" name="fecha_gasto" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="campo" style="grid-column: 1 / -1;">
                    <label>Descripción (opcional)</label>
                    <textarea name="descripcion" rows="3"></textarea>
                </div>
                <div class="campo" style="grid-column: 1 / -1;">
                    <label>Comprobante / factura (opcional)</label>
                    <input type="file" name="comprobante" accept="image/*,application/pdf">
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-primario">Guardar gasto</button>
            </div>
        </form>
    </div>
</div>
