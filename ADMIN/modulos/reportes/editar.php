<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

$id_reporte = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_reporte) {
    echo "<p>Reporte no válido.</p>";
    exit;
}

// Datos del reporte que se va a editar
$sql = "SELECT id_reporte, id_usuario, id_tipo_reporte, descripcion_reporte
        FROM reportes WHERE id_reporte = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_reporte]);
$reporte = $stmt->fetch();

if (!$reporte) {
    echo "<p>Reporte no encontrado.</p>";
    exit;
}

// Listas para llenar los <select> de usuario y tipo de reporte
$usuarios = $conexion->query("SELECT id_usuario, nombre, apellido FROM usuarios")->fetchAll();
$tipos_reporte = $conexion->query("SELECT id_tipo_reporte, tipo_reporte FROM tipo_reporte")->fetchAll();
?>

<h4>Editar reporte</h4>

<form action="modulos/reportes/actualizar.php" method="POST" class="formulario_editar" id="form_editar_reporte">

    <input type="hidden" name="id_reporte" value="<?= $reporte['id_reporte'] ?>">

    <div class="informacion">

        <div class="campo">
            <label>Usuario </label>
            <select name="id_usuario" required>
                <option value="">Selecciona…</option>
                <?php foreach ($usuarios as $u): // deja "selected" el usuario que ya tenía este reporte ?>
                <option value="<?= $u['id_usuario'] ?>" <?= $u['id_usuario'] == $reporte['id_usuario'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label>Tipo de reporte</label>
            <select name="id_tipo_reporte" required>
                <option value="">Selecciona…</option>
                <?php foreach ($tipos_reporte as $t): ?>
                <option value="<?= $t['id_tipo_reporte'] ?>" <?= $t['id_tipo_reporte'] == $reporte['id_tipo_reporte'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['tipo_reporte']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label>Descripción</label>
            <textarea name="descripcion_reporte" required maxlength="255"><?= htmlspecialchars($reporte['descripcion_reporte']) ?></textarea>
        </div>

    </div>

    <div class="form-acciones">
        <button type="button" class="btn-secundario" data-cerrar-modal>Cancelar</button>
        <button type="submit" class="btn-primario">Guardar Cambios</button>
    </div>
</form>