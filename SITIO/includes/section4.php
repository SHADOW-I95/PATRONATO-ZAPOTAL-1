<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
$conexion = Connection();

// Tipos de reporte reales (antes venían inventados en el HTML y no
// coincidían con los ids de la tabla tipo_reporte)
$tipos_reporte = $conexion->query("SELECT id_tipo_reporte, tipo_reporte FROM tipo_reporte")->fetchAll();

$mensaje_reporte = '';

if (isset($_POST['enviar']) && esUsuarioComun()) {

    $tipo        = (int) $_POST['tipo_reporte'];
    $descripcion = trim($_POST['descripcion']);

    $sql  = "INSERT INTO reportes (id_usuario, id_tipo_reporte, descripcion_reporte) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$_SESSION['id'], $tipo, $descripcion]);

    $mensaje_reporte = 'Reporte guardado correctamente.';
}
?>

<div class="div4" id="section4">
    <div class="contenedor">
        <h2>Reportar Problema</h2>

        <?php if ($mensaje_reporte): ?>
            <p class="reporte-exito"><?= htmlspecialchars($mensaje_reporte) ?></p>
        <?php endif; ?>

        <?php if (!esUsuarioComun()): ?>
            <p>Debes <a href="login/login.php">iniciar sesión</a> como usuario para reportar un problema.</p>
        <?php else: ?>
        <form method="POST">
            <label>Tipo de Reporte</label>
            <select name="tipo_reporte" required>
                <option value="">Seleccione una opción</option>
                <?php foreach ($tipos_reporte as $t): ?>
                <option value="<?= $t['id_tipo_reporte'] ?>"><?= htmlspecialchars(ucfirst($t['tipo_reporte'])) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Descripción</label>
            <textarea name="descripcion" rows="5" placeholder="Describa el problema..." required></textarea>

            <button type="submit" name="enviar">Enviar Reporte</button>
        </form>
        <?php endif; ?>
    </div>
</div>