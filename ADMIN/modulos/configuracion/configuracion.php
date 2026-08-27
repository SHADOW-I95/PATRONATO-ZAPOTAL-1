<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";
require_once __DIR__ . "/../../../config/configuracion_general.php";
$conexion = Connection();

$es_admin = esAdministrador();

// =======================
// DATOS DEL PATRONATO (solo Administrador)
// =======================
$datos_patronato = $es_admin ? obtenerConfiguracionGeneral() : [];

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

// =======================
// DESCUENTOS POR EDAD (solo Administrador)
// =======================
$descuentos_edad = [];

if ($es_admin) {
    $descuentos_edad = $conexion->query("SELECT id_descuento, descripcion, edad_minima, monto_descuento FROM descuentos_edad ORDER BY edad_minima DESC")->fetchAll();
}

// =======================
// ROLES Y PERMISOS (solo Administrador)
// =======================
$roles_editables = [];
$modulos_sistema = [];
$permisos_actuales = []; // [id_rol][clave_modulo] = true

if ($es_admin) {
    // El rol Administrador (id 3) nunca aparece aquí: siempre tiene todo,
    // no se edita ni se muestra como si fuera configurable.
    $roles_editables = $conexion->query("SELECT id_roles, nombre_rol FROM roles WHERE id_roles <> 3 ORDER BY nombre_rol")->fetchAll();
    $modulos_sistema = $conexion->query("SELECT id_modulo, clave, nombre_visible FROM modulos_sistema ORDER BY orden")->fetchAll();

    $stmtPermisos = $conexion->query(
        "SELECT pr.id_rol, m.clave FROM permisos_rol pr INNER JOIN modulos_sistema m ON pr.id_modulo = m.id_modulo"
    );
    foreach ($stmtPermisos->fetchAll() as $p) {
        $permisos_actuales[$p['id_rol']][$p['clave']] = true;
    }
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
<?php elseif (($_GET['error'] ?? '') === 'nombre_vacio'): ?>
<div class="aviso-exito" style="background:#fef2f2; border-color:#fecaca; color:#991b1b;">
    El nombre del patronato no puede quedar vacío.
</div>
<?php elseif (($_GET['error'] ?? '') === 'logo_invalido'): ?>
<div class="aviso-exito" style="background:#fef2f2; border-color:#fecaca; color:#991b1b;">
    El logo debe ser una imagen (jpg, png o webp).
</div>
<?php elseif (($_GET['error'] ?? '') === 'logo_muy_grande'): ?>
<div class="aviso-exito" style="background:#fef2f2; border-color:#fecaca; color:#991b1b;">
    El logo es muy pesado (máximo 3MB).
</div>
<?php elseif (($_GET['error'] ?? '') === 'error_guardando'): ?>
<div class="aviso-exito" style="background:#fef2f2; border-color:#fecaca; color:#991b1b;">
    Ocurrió un error al guardar. Intenta de nuevo.
</div>
<?php elseif (($_GET['mensaje'] ?? '') === 'actualizado'): ?>
<div class="aviso-exito">
    Datos guardados correctamente.
</div>
<?php endif; ?>

<!-- ==================== PESTAÑAS ==================== -->
<div class="tabs-config">
    <?php if ($es_admin): ?>
    <button type="button" class="tab-btn activo" data-tab="tab-patronato">Datos del patronato</button>
    <button type="button" class="tab-btn" data-tab="tab-catalogos">Catálogos</button>
    <button type="button" class="tab-btn" data-tab="tab-descuentos">Descuentos</button>
    <button type="button" class="tab-btn" data-tab="tab-roles">Roles y permisos</button>
    <?php endif; ?>
    <button type="button" class="tab-btn <?= $es_admin ? '' : 'activo' ?>" data-tab="tab-cuenta">Mi cuenta</button>
</div>

<!-- ==================== TAB: DATOS DEL PATRONATO ==================== -->
<?php if ($es_admin): ?>
<div class="tab-panel activo" id="tab-patronato">
    <div class="seccion" style="max-width: 640px;">
        <h3>Información general</h3>
        <p class="ayuda-catalogo">
            Estos datos se muestran en el sitio público (header, footer, login) y en el panel ADMIN.
            La cuenta bancaria es la que ve el usuario cuando va a pagar.
        </p>

        <form id="form-datos-patronato" action="modulos/configuracion/configuracion_general_actualizar.php" method="POST" enctype="multipart/form-data" class="formulario">
            <div class="informacion">
                <div class="campo" style="grid-column: 1 / -1;">
                    <label>Logo actual</label>
                    <div style="display:flex; align-items:center; gap:14px; margin-top:6px;">
                        <img src="../SITIO/<?= htmlspecialchars($datos_patronato['logo_path'] ?? 'assets/img/LOGO.png') ?>" alt="Logo actual" style="width:56px; height:56px; object-fit:contain; border:1px solid var(--borde); border-radius:8px; padding:4px;">
                        <input type="file" name="logo" accept="image/*">
                    </div>
                </div>
                <div class="campo">
                    <label>Nombre del patronato</label>
                    <input type="text" name="nombre_patronato" required maxlength="100" value="<?= htmlspecialchars($datos_patronato['nombre_patronato'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Teléfono de contacto</label>
                    <input type="text" name="telefono_contacto" maxlength="30" value="<?= htmlspecialchars($datos_patronato['telefono_contacto'] ?? '') ?>">
                </div>
                <div class="campo" style="grid-column: 1 / -1;">
                    <label>Dirección</label>
                    <input type="text" name="direccion" maxlength="150" value="<?= htmlspecialchars($datos_patronato['direccion'] ?? '') ?>">
                </div>
            </div>

            <h3 style="margin-top:22px;">Cuenta bancaria (para pagos)</h3>
            <div class="informacion">
                <div class="campo">
                    <label>Banco</label>
                    <input type="text" name="banco_nombre" maxlength="80" value="<?= htmlspecialchars($datos_patronato['banco_nombre'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Número de cuenta</label>
                    <input type="text" name="banco_cuenta" maxlength="50" value="<?= htmlspecialchars($datos_patronato['banco_cuenta'] ?? '') ?>">
                </div>
                <div class="campo" style="grid-column: 1 / -1;">
                    <label>A nombre de</label>
                    <input type="text" name="banco_titular" maxlength="100" value="<?= htmlspecialchars($datos_patronato['banco_titular'] ?? '') ?>">
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-primario">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ==================== TAB: CATÁLOGOS ==================== -->
<?php if ($es_admin): ?>
<div class="tab-panel" id="tab-catalogos">

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

<!-- ==================== TAB: DESCUENTOS POR EDAD ==================== -->
<?php if ($es_admin): ?>
<div class="tab-panel" id="tab-descuentos">

    <p class="ayuda-catalogo">
        Se aplican automáticamente según la edad del dueño/a de la vivienda
        (se calcula con su fecha de nacimiento). Si alguien califica para
        más de un descuento a la vez, solo se aplica el de la edad mínima
        más alta que cumpla — no se suman entre sí.
    </p>

    <div class="seccion">
        <h3>Agregar descuento</h3>
        <form class="form-descuento" id="form-nuevo-descuento" action="modulos/configuracion/descuento_agregar.php" method="POST">
            <div class="campo-descuento">
                <label>Descripción</label>
                <input type="text" name="descripcion" placeholder="Ej. Adulto mayor" required maxlength="50">
            </div>
            <div class="campo-descuento">
                <label>Edad mínima (o más)</label>
                <input type="number" name="edad_minima" min="0" max="120" required>
            </div>
            <div class="campo-descuento">
                <label>Descuento (L)</label>
                <input type="number" name="monto_descuento" min="0" step="0.01" required>
            </div>
            <button type="submit" class="btn-primario">Agregar</button>
        </form>
    </div>

    <div class="seccion">
        <h3>Descuentos configurados</h3>
        <table class="tabla_datos">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Edad mínima</th>
                    <th>Descuento</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($descuentos_edad as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['descripcion']) ?></td>
                    <td><?= (int) $d['edad_minima'] ?> años o más</td>
                    <td>L<?= number_format($d['monto_descuento'], 2) ?></td>
                    <td class="col-acciones">
                        <a class="btn-eliminar" href="modulos/configuracion/descuento_eliminar.php?id=<?= $d['id_descuento'] ?>"
                            onclick="return confirm('¿Eliminar este descuento?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$descuentos_edad): ?>
                <tr><td colspan="4">Todavía no hay descuentos configurados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ==================== TAB: ROLES Y PERMISOS ==================== -->
<?php if ($es_admin): ?>
<div class="tab-panel" id="tab-roles">

    <p class="ayuda-catalogo">
        El Administrador siempre tiene acceso a todo el sistema — no aparece
        aquí porque no se puede editar. Los demás roles solo ven los módulos
        que marques abajo.
    </p>

    <div class="seccion">
        <h3>Agregar rol nuevo</h3>
        <form class="form-catalogo" id="form-nuevo-rol" action="modulos/configuracion/rol_agregar.php" method="POST">
            <input type="text" name="nombre_rol" placeholder="Nombre del nuevo rol (ej. Supervisor)" required maxlength="40">
            <button type="submit" class="btn-primario">Agregar rol</button>
        </form>
    </div>

    <?php foreach ($roles_editables as $rol): ?>
    <div class="seccion">
        <h3><?= htmlspecialchars($rol['nombre_rol']) ?></h3>

        <form class="form-permisos" data-id-rol="<?= $rol['id_roles'] ?>">
            <div class="permisos-lista">
                <?php foreach ($modulos_sistema as $mod): ?>
                <label class="permiso-item">
                    <input type="checkbox" name="modulos[]" value="<?= htmlspecialchars($mod['clave']) ?>"
                        <?= isset($permisos_actuales[$rol['id_roles']][$mod['clave']]) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($mod['nombre_visible']) ?>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn-secundario">Guardar permisos de <?= htmlspecialchars($rol['nombre_rol']) ?></button>
        </form>
    </div>
    <?php endforeach; ?>

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