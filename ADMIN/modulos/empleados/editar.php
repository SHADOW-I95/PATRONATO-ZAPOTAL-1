<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/auth.php";

// Al resto del módulo (empleados.php, ver.php, actualizar.php, eliminar.php)
// ya se le validaba esAdministrador(); a este formulario le faltaba, y sin
// esto cualquier empleado con sesión iniciada podía pedir este archivo
// directamente y ver/editar los datos de otros empleados.
if (!esAdministrador()) {
    echo '<p>No tienes permisos para editar empleados.</p>';
    exit;
}

$conexion = Connection();

$id_empleado = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_empleado) {
    echo "<p>Empleado no válido.</p>";
    exit;
}

$sql = "SELECT id_empleado, dni, nombre, apellido, fecha_nacimiento, telefono, codigo
        FROM empleados WHERE id_empleado = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_empleado]);
$empleado = $stmt->fetch();

if (!$empleado) {
    echo "<p>Empleado no encontrado.</p>";
    exit;
}
?>

<h4>Editar empleado</h4>

<form action="modulos/empleados/actualizar.php" method="POST" class="formulario_editar" id="form_editar_empleado">

    <input type="hidden" name="id_empleado" value="<?= $empleado['id_empleado'] ?>">

    <div class="informacion">

        <div class="campo">
            <label>DNI</label>
            <input type="text" name="DNI" required maxlength="20" value="<?= htmlspecialchars($empleado['dni']) ?>">
        </div>
        <div class="campo">
            <label>Código de acceso</label>
            <input type="text" name="codigo" required maxlength="50" value="<?= htmlspecialchars($empleado['codigo']) ?>">
        </div>
        <div class="campo">
            <label>Nombre</label>
            <input type="text" name="nombre" required maxlength="30" value="<?= htmlspecialchars($empleado['nombre']) ?>">
        </div>
        <div class="campo">
            <label>Apellido</label>
            <input type="text" name="apellido" required maxlength="30" value="<?= htmlspecialchars($empleado['apellido']) ?>">
        </div>
        <div class="campo">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($empleado['fecha_nacimiento'] ?? '') ?>">
        </div>
        <div class="campo">
            <label>Teléfono</label>
            <input type="text" name="telefono" maxlength="30" value="<?= htmlspecialchars($empleado['telefono'] ?? '') ?>">
        </div>
    </div>

    <div class="form-acciones">
        <button type="button" class="btn-secundario" data-cerrar-modal>Cancelar</button>
        <button type="submit" class="btn-primario">Guardar Cambios</button>
    </div>
</form>