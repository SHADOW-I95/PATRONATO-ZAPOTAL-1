<?php
require_once __DIR__ . '/../../config/conexion.php'; // conexión a la base de datos
require_once __DIR__ . '/../../config/auth.php';     // autenticación de usuarios
$conexion = Connection();

// Obtiene los tipos de reporte desde la tabla tipo_reporte
$tipos_reporte = $conexion->query("SELECT id_tipo_reporte, tipo_reporte FROM tipo_reporte")->fetchAll();

$mensaje_reporte = '';

// Si se envía el formulario y el usuario es común
if (isset($_POST['enviar']) && esUsuarioComun()) {
    $tipo        = (int) $_POST['tipo_reporte']; // id del tipo de reporte
    $descripcion = trim($_POST['descripcion']); // descripción del problema

    // Inserta el reporte en la base de datos
    $sql  = "INSERT INTO reportes (id_usuario, id_tipo_reporte, descripcion_reporte) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$_SESSION['id'], $tipo, $descripcion]);

    $mensaje_reporte = 'Reporte guardado correctamente.'; // mensaje de éxito
}
?>

<div class="div4" id="section4">
    <div class="contenedor">
        <h2>Reportar Problema</h2>

        <!-- Mensaje de éxito -->
        <?php if ($mensaje_reporte): ?>
            <p class="reporte-exito"><?= htmlspecialchars($mensaje_reporte) ?></p>
        <?php endif; ?>

        <!-- Si el usuario no es común, se pide iniciar sesión -->
        <?php if (!esUsuarioComun()): ?>
            <p>Debes <a href="login/login.php">iniciar sesión</a> como usuario para reportar un problema.</p>
        <?php else: ?>
        
        <!-- Formulario de reporte -->
        <form method="POST">
            <label>Tipo de Reporte</label>
            <select name="tipo_reporte" required>
                <option value="">Seleccione una opción</option>
                <?php foreach ($tipos_reporte as $t): ?>
                <option value="<?= $t['id_tipo_reporte'] ?>">
                    <?= htmlspecialchars(ucfirst($t['tipo_reporte'])) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label>Descripción</label>
            <textarea name="descripcion" rows="5" placeholder="Describa el problema..." required></textarea>

            <button type="submit" name="enviar">Enviar Reporte</button>
        </form>
        <?php endif; ?>
    </div>
</div>
