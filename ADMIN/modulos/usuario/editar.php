<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";

if (!tienePermiso('usuario')) {
    http_response_code(403);
    exit;
}

$conexion = Connection();

$id_usuario = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_usuario) {
    echo "<p>Usuario no válido.</p>";
    exit;
}

// Datos del usuario que se va a editar
$sql_usuario = "SELECT id_usuario, dni, codigo, nombre, apellido, fecha_nacimiento, telefono
                FROM usuarios WHERE id_usuario = ?";
$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->execute([$id_usuario]);
$usuario = $stmt_usuario->fetch();

if (!$usuario) {
    echo "<p>Usuario no encontrado.</p>";
    exit;
}

// Viviendas que ya tiene este usuario (para precargar una fila por cada una)
$sql_viviendas = "SELECT id_vivienda, numero_vivienda, id_sector, id_servicio, cuota, id_estado_pago
                  FROM viviendas WHERE id_usuario = ?";
$stmt_viviendas = $conexion->prepare($sql_viviendas);
$stmt_viviendas->execute([$id_usuario]);
$viviendas = $stmt_viviendas->fetchAll();

// Listas para llenar los <select> de sector, servicio y estado
$sectores    = $conexion->query("SELECT id_sector, nombre_sector FROM sectores")->fetchAll();
$servicios   = $conexion->query("SELECT id_servicio, nombre_servicio FROM servicios")->fetchAll();
$estado_pago = $conexion->query("SELECT id_estado_pago, nombre_estado_pago FROM estado_pago")->fetchAll();
?>

<h4>Editar usuario</h4>

<form action="modulos/usuario/actualizar.php" method="POST" class="formulario_editar" id="form_editar">

    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
    <!-- Aquí usuario.js va guardando, separados por coma, los id_vivienda que se quiten con "Quitar vivienda" -->
    <input type="hidden" name="viviendas_eliminar" id="editar_viviendas_eliminar" value="">

    <div class="informacion">

        <div class="campo">
            <label>DNI </label>
            <input type="text" name="DNI" required maxlength="20" value="<?= htmlspecialchars($usuario['dni']) ?>">
        </div>
        <div class="campo">
            <label>Código de acceso</label>
            <input type="text" name="codigo" required maxlength="50" value="<?= htmlspecialchars($usuario['codigo']) ?>">
        </div>
        <div class="campo">
            <label>Nombre </label>
            <input type="text" name="nombre" required maxlength="60" value="<?= htmlspecialchars($usuario['nombre']) ?>">
        </div>
        <div class="campo">
            <label>Apellido </label>
            <input type="text" name="apellido" required maxlength="60" value="<?= htmlspecialchars($usuario['apellido']) ?>">
        </div>
        <div class="campo">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento'] ?? '') ?>">
        </div>
        <div class="campo">
            <label>Teléfono</label>
            <input type="text" name="telefono" maxlength="20" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
        </div>
    </div>

    <div id="editar_contenedor_viviendas">
        <?php foreach ($viviendas as $i => $v): ?>
        <div class="vivienda-fila">
            <hr>
            <!-- id real de la vivienda: si viene vacío al guardar, actualizar.php la trata como nueva -->
            <input type="hidden" name="vivienda[<?= $i ?>][id]" value="<?= $v['id_vivienda'] ?>">

            <div class="vivienda">
                <div class="campo">
                    <label>Vivienda</label>
                    <input type="text" name="vivienda[<?= $i ?>][numero]" value="<?= htmlspecialchars($v['numero_vivienda']) ?>">
                </div>

                <div class="campo">
                    <label>Sector</label>
                    <select name="vivienda[<?= $i ?>][sector]" required>
                        <option value="">Selecciona…</option>
                        <?php foreach ($sectores as $s): ?>
                        <option value="<?= $s['id_sector'] ?>" <?= $s['id_sector'] == $v['id_sector'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre_sector']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Tipo servicio</label>
                    <select name="vivienda[<?= $i ?>][servicio]" required>
                        <option value="">Selecciona…</option>
                        <?php foreach ($servicios as $s): ?>
                        <option value="<?= $s['id_servicio'] ?>" <?= $s['id_servicio'] == $v['id_servicio'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre_servicio']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Cuota mensual (L)</label>
                    <input type="number" step="0.01" min="0" name="vivienda[<?= $i ?>][cuota]" value="<?= htmlspecialchars($v['cuota']) ?>">
                </div>

                <div class="campo">
                    <label>Estado</label>
                    <select name="vivienda[<?= $i ?>][estado]">
                        <option value="">Selecion…</option>
                        <?php foreach ($estado_pago as $estado): ?>
                        <option value="<?= $estado['id_estado_pago'] ?>" <?= $estado['id_estado_pago'] == $v['id_estado_pago'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($estado['nombre_estado_pago']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- usuario.js le agrega el evento: si tiene id, la marca para borrar; si no, solo la quita del formulario -->
            <button type="button" class="btn-secundario btn-quitar-vivienda">Quitar vivienda</button>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Plantilla para "Agregar vivienda": no se muestra en pantalla, usuario.js solo la clona -->
    <template id="plantilla-vivienda-editar">
        <div class="vivienda-fila">
            <hr>
            <input type="hidden" name="vivienda[__INDICE__][id]" value="">

            <div class="vivienda">
                <div class="campo">
                    <label>Vivienda</label>
                    <input type="text" name="vivienda[__INDICE__][numero]" placeholder="Número de vivienda">
                </div>

                <div class="campo">
                    <label>Sector</label>
                    <select name="vivienda[__INDICE__][sector]" required>
                        <option value="">Selecciona…</option>
                        <?php foreach ($sectores as $s): ?>
                        <option value="<?= $s['id_sector'] ?>"><?= htmlspecialchars($s['nombre_sector']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Tipo servicio</label>
                    <select name="vivienda[__INDICE__][servicio]" required>
                        <option value="">Selecciona…</option>
                        <?php foreach ($servicios as $s): ?>
                        <option value="<?= $s['id_servicio'] ?>"><?= htmlspecialchars($s['nombre_servicio']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Cuota mensual (L)</label>
                    <input type="number" step="0.01" min="0" name="vivienda[__INDICE__][cuota]" value="0">
                </div>

                <div class="campo">
                    <label>Estado</label>
                    <select name="vivienda[__INDICE__][estado]">
                        <option value="">Selecion…</option>
                        <?php foreach ($estado_pago as $estado): ?>
                        <option value="<?= $estado['id_estado_pago'] ?>"><?= htmlspecialchars($estado['nombre_estado_pago']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="button" class="btn-secundario btn-quitar-vivienda">Quitar vivienda</button>
        </div>
    </template>

    <div class="form-acciones">
        <button type="button" id="editar_agregar_vivienda" class="btn btn-terceareo">Agregar vivienda</button>
        <button type="button" class="btn-secundario" data-cerrar-modal>Cancelar</button>
        <button type="submit" class="btn-primario">Guardar Cambios</button>
    </div>
</form>