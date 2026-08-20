<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
$conexion = Connection();

$es_admin = esAdministrador();

// =======================
// MI CUENTA (cualquier empleado logueado)
// =======================
$id_empleado_sesion = (int) ($_SESSION['id'] ?? 0);

$sql_mi_cuenta = "SELECT e.id_empleado, e.dni, e.nombre, e.apellido, e.telefono, e.codigo,
                          e.fecha_registro, r.nombre_rol
                   FROM empleados e
                   LEFT JOIN roles r ON e.id_rol = r.id_roles
                   WHERE e.id_empleado = ?";
$stmt_mi_cuenta = $conexion->prepare($sql_mi_cuenta);
$stmt_mi_cuenta->execute([$id_empleado_sesion]);
$mi_cuenta = $stmt_mi_cuenta->fetch();

// =======================
// CATÁLOGOS (solo Administrador)
// =======================
$sectores = $servicios = $tipos_reporte = [];

if ($es_admin) {
    $sectores      = $conexion->query("SELECT id_sector, nombre_sector FROM sectores ORDER BY nombre_sector")->fetchAll();
    $servicios     = $conexion->query("SELECT id_servicio, nombre_servicio FROM servicios ORDER BY nombre_servicio")->fetchAll();
    $tipos_reporte = $conexion->query("SELECT id_tipo_reporte, tipo_reporte FROM tipo_reporte ORDER BY tipo_reporte")->fetchAll();
}
?>

<div class="modulo_header">
    <div class="encabezado">
        <h1>Configuración</h1>
    </div>
</div>

<?php if (($_GET['error'] ?? '') === 'en_uso'): ?>
<div class="aviso-exito" style="background:#fef2f2; border-color:#fecaca; color:#991b1b;">
    No se pudo eliminar: ese valor todavía está siendo usado por alguna vivienda o reporte.
    Reasigna esos registros a otro valor antes de eliminarlo.
</div>
<?php elseif (($_GET['error'] ?? '') === 'error_eliminando'): ?>
<div class="aviso-exito" style="background:#fef2f2; border-color:#fecaca; color:#991b1b;">
    Ocurrió un error al eliminar. Intenta de nuevo.
</div>
<?php endif; ?>

<!-- ==================== PESTAÑAS ==================== -->
<div class="tabs-config">
    <?php if ($es_admin): ?>
    <button type="button" class="tab-btn activo" data-tab="tab-catalogos">Catálogos</button>
    <?php endif; ?>
    <button type="button" class="tab-btn <?= $es_admin ? '' : 'activo' ?>" data-tab="tab-cuenta">Mi cuenta</button>
</div>

<!-- ==================== TAB: CATÁLOGOS ==================== -->
<?php if ($es_admin): ?>
<div class="tab-panel activo" id="tab-catalogos">

    <p class="ayuda-catalogo">
        Estos son los valores que se usan en los formularios de todo el sistema
        (sectores, tipos de servicio y tipos de reporte). Si un valor está en uso
        por alguna vivienda o reporte, no se podrá eliminar hasta que se le
        reasigne a otro.
    </p>

    <div class="catalogos-grid">

        <!-- ---------- SECTORES ---------- -->
        <div class="seccion catalogo-caja">
            <h3>Sectores</h3>

            <form class="form-catalogo" data-tipo="sector" action="modulos/configuracion/catalogo_agregar.php" method="POST">
                <input type="hidden" name="tipo" value="sector">
                <input type="text" name="nombre" placeholder="Nombre del nuevo sector" required maxlength="50">
                <button type="submit" class="btn-primario">Agregar</button>
            </form>

            <table class="tabla_datos tabla-catalogo">
                <tbody>
                    <?php foreach ($sectores as $s): ?>
                    <tr data-id="<?= $s['id_sector'] ?>">
                        <td><?= htmlspecialchars($s['nombre_sector']) ?></td>
                        <td class="col-acciones">
                            <button type="button" class="btn-editar btn-editar-catalogo"
                                data-tipo="sector" data-id="<?= $s['id_sector'] ?>"
                                data-nombre="<?= htmlspecialchars($s['nombre_sector']) ?>">Editar</button>
                            <a class="btn-eliminar"
                                href="modulos/configuracion/catalogo_eliminar.php?tipo=sector&id=<?= $s['id_sector'] ?>"
                                onclick="return confirm('¿Eliminar este sector?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$sectores): ?>
                    <tr>
                        <td colspan="2">Todavía no hay sectores registrados.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ---------- TIPOS DE SERVICIO ---------- -->
        <div class="seccion catalogo-caja">
            <h3>Tipos de servicio</h3>

            <form class="form-catalogo" data-tipo="servicio" action="modulos/configuracion/catalogo_agregar.php" method="POST">
                <input type="hidden" name="tipo" value="servicio">
                <input type="text" name="nombre" placeholder="Nombre del nuevo servicio" required maxlength="30">
                <button type="submit" class="btn-primario">Agregar</button>
            </form>

            <table class="tabla_datos tabla-catalogo">
                <tbody>
                    <?php foreach ($servicios as $s): ?>
                    <tr data-id="<?= $s['id_servicio'] ?>">
                        <td><?= htmlspecialchars($s['nombre_servicio']) ?></td>
                        <td class="col-acciones">
                            <button type="button" class="btn-editar btn-editar-catalogo"
                                data-tipo="servicio" data-id="<?= $s['id_servicio'] ?>"
                                data-nombre="<?= htmlspecialchars($s['nombre_servicio']) ?>">Editar</button>
                            <a class="btn-eliminar"
                                href="modulos/configuracion/catalogo_eliminar.php?tipo=servicio&id=<?= $s['id_servicio'] ?>"
                                onclick="return confirm('¿Eliminar este tipo de servicio?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$servicios): ?>
                    <tr>
                        <td colspan="2">Todavía no hay tipos de servicio registrados.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ---------- TIPOS DE REPORTE ---------- -->
        <div class="seccion catalogo-caja">
            <h3>Tipos de reporte</h3>

            <form class="form-catalogo" data-tipo="tipo_reporte" action="modulos/configuracion/catalogo_agregar.php" method="POST">
                <input type="hidden" name="tipo" value="tipo_reporte">
                <input type="text" name="nombre" placeholder="Nombre del nuevo tipo" required maxlength="100">
                <button type="submit" class="btn-primario">Agregar</button>
            </form>

            <table class="tabla_datos tabla-catalogo">
                <tbody>
                    <?php foreach ($tipos_reporte as $t): ?>
                    <tr data-id="<?= $t['id_tipo_reporte'] ?>">
                        <td><?= htmlspecialchars($t['tipo_reporte']) ?></td>
                        <td class="col-acciones">
                            <button type="button" class="btn-editar btn-editar-catalogo"
                                data-tipo="tipo_reporte" data-id="<?= $t['id_tipo_reporte'] ?>"
                                data-nombre="<?= htmlspecialchars($t['tipo_reporte']) ?>">Editar</button>
                            <a class="btn-eliminar"
                                href="modulos/configuracion/catalogo_eliminar.php?tipo=tipo_reporte&id=<?= $t['id_tipo_reporte'] ?>"
                                onclick="return confirm('¿Eliminar este tipo de reporte?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$tipos_reporte): ?>
                    <tr>
                        <td colspan="2">Todavía no hay tipos de reporte registrados.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
<?php endif; ?>

<!-- ==================== TAB: MI CUENTA ==================== -->
<div class="tab-panel <?= $es_admin ? '' : 'activo' ?>" id="tab-cuenta">

    <div class="seccion cuenta-caja">
        <h3>Mi cuenta</h3>

        <?php if (!$mi_cuenta): ?>
        <p>No se pudo cargar tu información. Vuelve a iniciar sesión.</p>
        <?php else: ?>
        <form id="form_mi_cuenta" action="modulos/configuracion/cuenta_actualizar.php" method="POST" class="formulario">
            <div class="informacion">

                <div class="campo">
                    <label>DNI</label>
                    <input type="text" value="<?= htmlspecialchars($mi_cuenta['dni']) ?>" disabled>
                </div>
                <div class="campo">
                    <label>Nombre completo</label>
                    <input type="text" value="<?= htmlspecialchars($mi_cuenta['nombre'] . ' ' . $mi_cuenta['apellido']) ?>" disabled>
                </div>
                <div class="campo">
                    <label>Rol</label>
                    <input type="text" value="<?= htmlspecialchars($mi_cuenta['nombre_rol'] ?? '—') ?>" disabled>
                </div>
                <div class="campo">
                    <label>Empleado desde</label>
                    <input type="text" value="<?= htmlspecialchars($mi_cuenta['fecha_registro']) ?>" disabled>
                </div>

                <div class="campo">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" maxlength="30" value="<?= htmlspecialchars($mi_cuenta['telefono'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Código de acceso</label>
                    <input type="text" name="codigo" required maxlength="50" value="<?= htmlspecialchars($mi_cuenta['codigo']) ?>">
                </div>

            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-primario">Guardar cambios</button>
            </div>
        </form>
        <?php endif; ?>
    </div>

</div>