<?php
require_once __DIR__ . "/../../config/conexion.php";
$conexion = Connection();

// Filtros que llegan por GET (tipo de reporte y texto de búsqueda)
$where = [];
if (!empty($_GET['tipo'])) {
    $where[] = "reportes.id_tipo_reporte = :tipo";
}
if (!empty($_GET['buscar'])) {
    $where[] = "(reportes.descripcion_reporte LIKE :buscar 
                OR reportes.id_reporte LIKE :buscar 
                OR reportes.id_usuario IN (
                    SELECT id_usuario FROM usuarios 
                    WHERE nombre LIKE :buscar OR apellido LIKE :buscar
                ))";
}

// Trae cada reporte con sus datos en crudo (sin JOIN)
$sql_reportes = "SELECT 
  id_reporte,
  id_usuario,
  id_tipo_reporte,
  descripcion_reporte
  FROM reportes";

if (count($where) > 0) {
    $sql_reportes .= " WHERE " . implode(" AND ", $where);
}
$sql_reportes .= " ORDER BY reportes.id_reporte DESC";

$stmt_reportes = $conexion->prepare($sql_reportes);
if (!empty($_GET['tipo'])) {
    $stmt_reportes->bindValue(":tipo", $_GET['tipo'], PDO::PARAM_INT);
}
if (!empty($_GET['buscar'])) {
    $stmt_reportes->bindValue(":buscar", "%" . $_GET['buscar'] . "%");
}
$stmt_reportes->execute();
$reportes = $stmt_reportes->fetchAll();

// Usuarios: se usan para llenar el <select> del formulario de nuevo reporte
$sql_usuarios =
 "SELECT id_usuario,
  nombre,
  apellido
  FROM usuarios";
$stmt_usuarios = $conexion->prepare($sql_usuarios);
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->fetchAll();

// Tipos de reporte: se traen de la tabla tipo_reporte (llave foránea real)
$sql_tipos =
 "SELECT id_tipo_reporte,
  tipo_reporte
  FROM tipo_reporte";
$stmt_tipos = $conexion->prepare($sql_tipos);
$stmt_tipos->execute();
$tipos_reporte = $stmt_tipos->fetchAll();
?>

<div class="modulo_header">
    <div class="encabezado">
        <h1>Reportes</h1>
    </div>
    <div class="opciones">
        <form method="GET" action="" class="filtros" id="form_filtros">
            <input type="hidden" name="modulo" value="reportes">
            <input type="text" name="buscar" placeholder="Buscar reporte..." class="buscar"
                value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">

            <select name="tipo">
                <option value="">Todos</option>
                <?php foreach ($tipos_reporte as $t): // Un <option> por cada tipo de reporte de la base de datos ?>
                <option value="<?= $t['id_tipo_reporte'] ?>"
                    <?= (($_GET['tipo'] ?? '') == $t['id_tipo_reporte']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['tipo_reporte']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>

        <button class="btn_nuevo" id="abrir-modal">
            + Nuevo Reporte
        </button>
    </div>
</div>

<!--===============================MODAL: NUEVO REPORTE==================================================-->

<div class="modal" id="modal">
    <div class="modal-contenido">

        <!-- data-cerrar-modal: lo cierra modal.js, sin necesitar un id específico -->
        <span class="cerrar" data-cerrar-modal>✕</span>
        <h4>+Nuevo reporte</h4>
        <form action="modulos/reportes/agregar.php" method="POST" class="formulario" id="form_reporte">
            <div class="informacion">

                <div class="campo">
                    <label>Usuario </label>
                    <select name="id_usuario" required>
                        <option value="">Selecciona…</option>
                        <?php foreach ($usuarios as $u): // Un <option> por cada usuario de la base de datos ?>
                        <option value="<?= $u['id_usuario'] ?>">
                            <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Tipo de reporte</label>
                    <select name="id_tipo_reporte" required>
                        <option value="">Selecciona…</option>
                        <?php foreach ($tipos_reporte as $t): // Un <option> por cada tipo de reporte de la base de datos ?>
                        <option value="<?= $t['id_tipo_reporte'] ?>"><?= htmlspecialchars($t['tipo_reporte']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Descripción</label>
                    <textarea name="descripcion_reporte" required maxlength="255"
                        placeholder="Describa el reporte..."></textarea>
                </div>

            </div>

            <div class="form-acciones">
                <button type="button" id="cancelar" class="btn-secundario" data-cerrar-modal>Cancelar</button>
                <button type="submit" id="guardar_reporte" class="btn-primario">Guardar Reporte</button>
            </div>
        </form>

    </div>
</div>

<!--===============================MODAL: VER REPORTE==================================================-->

<div class="modal" id="modal_ver">
    <div class="modal-contenido">

        <span class="cerrar" data-cerrar-modal>✕</span>

        <h3>Información del reporte</h3>

        <!-- Se llena con fetch desde reporte.js (modulos/reportes/ver.php) -->
        <div id="contenido_ver">

        </div>

    </div>
</div>

<!--===============================MODAL: EDITAR REPORTE==================================================-->

<div class="modal" id="modal_editar">
    <div class="modal-contenido">

        <span class="cerrar" data-cerrar-modal>✕</span>

        <!-- Se llena con fetch desde reporte.js (modulos/reportes/editar.php) -->
        <div id="contenido_editar">

        </div>

    </div>
</div>

<!--==================================TABLA PRINCIPAL DE REPORTES==================================================-->

<div class="seccion">
    <table class="tabla_datos">

        <thead class="thead">
            <tr>
                <th>id_reporte</th>
                <th>id_usuario</th>
                <th>id_tipo_reporte</th>
                <th>descripcion_reporte</th>
                <th>ACCIONES</th>
            </tr>
        </thead>

        <tbody class="tbody">
            <?php foreach ($reportes as $r): // Una fila por cada reporte que trajo la consulta ?>
            <tr>
                <td><?= $r['id_reporte'] ?></td>
                <td><?= $r['id_usuario'] ?></td>
                <td><?= htmlspecialchars($r['id_tipo_reporte']) ?></td>
                <td><?= htmlspecialchars($r['descripcion_reporte']) ?></td>
                <td>
                    <!-- data-id: reporte.js lo usa para saber a cuál editar -->
                    <button class="btn-editar" data-id="<?= $r['id_reporte'] ?>">
                        Editar
                    </button>
                    <a class="btn-eliminar" href="modulos/reportes/eliminar.php?id=<?= $r['id_reporte'] ?>"
                        onclick="return confirm('¿Desea eliminar este reporte?')">
                        Eliminar
                    </a>
                    <!-- data-id: reporte.js lo usa para saber a cuál mostrar -->
                    <button class="btn-ver" data-id="<?= $r['id_reporte'] ?>">
                        Ver
                    </button>
                </td>

            </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>

<!--==========================  El filtro se envía solo (GET), sin necesidad de JS extra ============================================-->

<script>
// Al cambiar el <select> de tipo, se envía el formulario de filtros automáticamente
const selectTipo = document.querySelector('#form_filtros select[name="tipo"]');
selectTipo.addEventListener("change", () => {
    document.getElementById("form_filtros").submit();
});
</script>