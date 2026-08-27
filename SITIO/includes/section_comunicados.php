<?php
require_once __DIR__ . '/../../config/conexion.php';
$conexion = Connection();

$comunicados = $conexion->query(
    "SELECT titulo, descripcion, imagen_path, fecha_publicacion
     FROM comunicados
     ORDER BY fecha_publicacion DESC
     LIMIT 20"
)->fetchAll();
?>
<section class="section-comunicados" id="section5">
    <div class="section3-div1">
        <span class="section3-tag">Avisos</span>
        <h2>Comunicados del patronato</h2>
    </div>

    <?php if (!$comunicados): ?>
    <p class="sin-comunicados">Todavía no hay comunicados publicados. Vuelve a revisar más adelante.</p>
    <?php else: ?>
    <div class="lista-comunicados-publico">
        <?php foreach ($comunicados as $c): ?>
        <article class="comunicado-publico">
            <?php if ($c['imagen_path']): ?>
            <img src="<?= htmlspecialchars($c['imagen_path']) ?>" alt="" loading="lazy">
            <?php endif; ?>
            <div class="comunicado-publico-cuerpo">
                <span class="comunicado-publico-fecha"><?= date('d/m/Y', strtotime($c['fecha_publicacion'])) ?></span>
                <h3><?= htmlspecialchars($c['titulo']) ?></h3>
                <p><?= nl2br(htmlspecialchars($c['descripcion'])) ?></p>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
