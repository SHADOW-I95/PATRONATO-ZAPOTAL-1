<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
requerirPermiso('comunicados');
$conexion = Connection();

$sql = "SELECT c.id_comunicado, c.titulo, c.descripcion, c.imagen_path, c.fecha_publicacion,
               CONCAT(e.nombre, ' ', e.apellido) AS nombre_empleado
        FROM comunicados c
        INNER JOIN empleados e ON c.id_empleado_publico = e.id_empleado
        ORDER BY c.fecha_publicacion DESC";
$comunicados = $conexion->query($sql)->fetchAll();
?>

<div class="modulo_header">
    <div class="encabezado"><h1>Comunicados</h1></div>
</div>

<?php if (($_GET['mensaje'] ?? '') === 'publicado'): ?>
<div class="aviso-exito">Comunicado publicado — ya es visible en el sitio público.</div>
<?php endif; ?>

<div class="seccion">
    <div class="opciones">
        <button type="button" class="btn_nuevo" id="abrir-modal">+ Nuevo comunicado</button>
    </div>
</div>

<div class="seccion">
    <h3>Historial de comunicados</h3>

    <?php if (!$comunicados): ?>
    <p>Todavía no se ha publicado ningún comunicado.</p>
    <?php else: ?>
    <div class="lista-comunicados">
        <?php foreach ($comunicados as $c): ?>
        <div class="comunicado-card">
            <?php if ($c['imagen_path']): ?>
            <img src="../SITIO/<?= htmlspecialchars($c['imagen_path']) ?>" alt="" class="comunicado-imagen">
            <?php endif; ?>
            <div class="comunicado-cuerpo">
                <div class="comunicado-fecha"><?= date('d/m/Y h:i A', strtotime($c['fecha_publicacion'])) ?> — <?= htmlspecialchars($c['nombre_empleado']) ?></div>
                <h4><?= htmlspecialchars($c['titulo']) ?></h4>
                <p><?= nl2br(htmlspecialchars($c['descripcion'])) ?></p>
                <a class="btn-eliminar" href="modulos/comunicados/eliminar.php?id=<?= $c['id_comunicado'] ?>"
                    onclick="return confirm('¿Eliminar este comunicado? Ya no se verá en el sitio público.')">Eliminar</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal: Nuevo comunicado -->
<div class="modal" id="modal">
    <div class="modal-contenido">
        <span class="cerrar" data-cerrar-modal>✕</span>
        <form class="formulario" action="modulos/comunicados/agregar.php" method="POST" enctype="multipart/form-data">
            <h4>+ Nuevo comunicado</h4>

            <div class="campo">
                <label>Título</label>
                <input type="text" name="titulo" required maxlength="120" placeholder="Ej. Corte de agua programado">
            </div>
            <div class="campo" style="margin-top:10px;">
                <label>Descripción</label>
                <textarea name="descripcion" rows="5" required placeholder="Escribe el mensaje completo para la comunidad..."></textarea>
            </div>
            <div class="campo" style="margin-top:10px;">
                <label>Imagen (opcional)</label>
                <input type="file" name="imagen" accept="image/*">
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-primario">Publicar</button>
            </div>
        </form>
    </div>
</div>
