<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
requerirPermiso('usuario');
$conexion = Connection();

// Trae cada usuario junto con la cantidad de viviendas que tiene (LEFT JOIN + COUNT)
$sql_usuarios = "SELECT 
  usuarios.id_usuario,
  usuarios.dni,
  usuarios.nombre,
  usuarios.apellido,
  usuarios.telefono,
  usuarios.codigo,
  TIMESTAMPDIFF(YEAR, usuarios.fecha_nacimiento, CURDATE()) AS edad,
  COUNT(viviendas.id_vivienda) AS cantidad_viviendas
  FROM usuarios 
  LEFT JOIN viviendas
  ON usuarios.id_usuario = viviendas.id_usuario
  GROUP BY usuarios.id_usuario;";
$usuarios = $conexion->query($sql_usuarios)->fetchAll();

// Solicitudes de traspaso pendientes
$sql_traspasos = "SELECT st.id_solicitud, st.nombre_comprador, st.apellido_comprador, st.dni_comprador,
                          st.telefono_comprador, st.motivo, st.fecha_solicitud,
                          v.id_vivienda, v.numero_vivienda, s.nombre_sector,
                          CONCAT(u.nombre, ' ', u.apellido) AS nombre_actual, u.dni AS dni_actual
                   FROM solicitudes_traspaso st
                   INNER JOIN viviendas v ON st.id_vivienda = v.id_vivienda
                   LEFT JOIN sectores s ON v.id_sector = s.id_sector
                   INNER JOIN usuarios u ON st.id_usuario_actual = u.id_usuario
                   WHERE st.id_estado_solicitud = 1
                   ORDER BY st.fecha_solicitud ASC";
$traspasos_pendientes = $conexion->query($sql_traspasos)->fetchAll();

  $stmt_usuario = $conexion->prepare($sql_usuarios);
  $stmt_usuario->execute(); 
  $usuarios = 
  $stmt_usuario->fetchAll();

  // Sectores, servicios y estados de pago: se usan para llenar los <select> del formulario
  $sql_sectores =
   "SELECT id_sector,
    nombre_sector
    FROM sectores";
  $stmt_sectores = $conexion->prepare($sql_sectores);
  $stmt_sectores->execute();
  $sectores = 
  $stmt_sectores->fetchAll();

  $sql_servicios =
   "SELECT id_servicio,
    nombre_servicio
    FROM servicios";
  $stmt_servicios = $conexion->prepare($sql_servicios);
  $stmt_servicios->execute();
  $servicios = 
  $stmt_servicios->fetchAll();

  $sql_estado_pago =
  "SELECT id_estado_pago,
  nombre_estado_pago
  FROM estado_pago";
  $stmt_estado_pago = $conexion->prepare($sql_estado_pago);
  $stmt_estado_pago->execute();
  $estado_pago =
  $stmt_estado_pago->fetchAll();
  
?>

<div class="modulo_header">
    <div class="encabezado">
        <h1>Usuarios</h1>
    </div>
    <div class="opciones">
        <input type="text" placeholder="Nombre o DNI..." class="buscar">
        <button class="btn_nuevo" id="abrir-modal">
            + Nuevo Usuario
        </button>
        <button type="button" class="btn-secundario btn-notificaciones" id="abrir-modal-traspasos">
            🔄 Traspasos
            <?php if ($traspasos_pendientes): ?>
            <span class="contador-notificaciones"><?= count($traspasos_pendientes) ?></span>
            <?php endif; ?>
        </button>
    </div>
</div>

<!--===============================MODAL: NUEVO USUARIO==================================================-->

<div class="modal" id="modal">
    <div class="modal-contenido">

        <span class="cerrar" data-cerrar-modal>✕</span>
        <h4>+Nuevo usuario</h4>
        <form action="modulos/usuario/agregar.php" method="POST" class="formulario" id="form_usuario">
            <div class="informacion">

                <div class="campo">
                    <label>DNI </label>
                    <input type="text" name="DNI" required maxlength="20" placeholder="0801199912345">
                </div>
                <div class="campo">
                    <label>Código de acceso</label>
                    <input type="text" name="codigo" required maxlength="50" placeholder="Código que se le entregará">
                </div>
                <div class="campo">
                    <label>Nombre </label>
                    <input type="text" name="nombre" required maxlength="60">
                </div>
                <div class="campo">
                    <label>Apellido </label>
                    <input type="text" name="apellido" required maxlength="60">
                </div>
                <div class="campo">
                    <label>Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento">
                </div>
                <div class="campo">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" maxlength="20">
                </div>
            </div>

            <!-- Vivienda inicial (índice 0). Las que se agreguen con "Agregar vivienda" usan la <template> de abajo -->
            <div id="contenedor_viviendas">
                <div class="vivienda-fila">
                    <div class="vivienda">
                        <div class="campo">
                            <label>Vivienda </label>
                            <input type="text" name="vivienda[0][numero]" placeholder="Numero de vivienda">
                        </div>

                        <div class="campo">
                            <label>Sector </label>
                            <select name="vivienda[0][sector]" required>
                                <option value="">Selecciona…</option>
                                <?php foreach ($sectores as $s): ?>
                                <option value="<?= $s['id_sector'] ?>"><?= htmlspecialchars($s['nombre_sector']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label>tipo servicio</label>
                            <select name="vivienda[0][servicio]" required>
                                <option value="">Selecion…</option>
                                <?php foreach ($servicios as $s):?>
                                <option value="<?= $s['id_servicio']?>"><?= htmlspecialchars($s['nombre_servicio'])?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label>Cuota mensual (L)</label>
                            <input type="number" name="vivienda[0][cuota]">
                        </div>

                        <div class="campo">
                            <label>Estado</label>
                            <select name="vivienda[0][estado]">
                                <option value="">Selecion…</option>
                                <?php foreach ($estado_pago as $estado):?>
                                <option value="<?= $estado['id_estado_pago']?>">
                                    <?= htmlspecialchars($estado['nombre_estado_pago'])?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- usuario.js le agrega el evento: solo quita la fila del formulario (todavía no existe en la base de datos) -->
                    <button type="button" class="btn-secundario btn-quitar-vivienda">Quitar vivienda</button>
                </div>
            </div>

            <!--
                Plantilla para "Agregar vivienda": no se muestra en pantalla, usuario.js solo la clona.
                Así el .js no necesita PHP dentro (las opciones ya vienen renderizadas aquí una sola vez).
            -->
            <template id="plantilla-vivienda-nuevo">
                <div class="vivienda-fila">
                    <hr>
                    <h4>Vivienda __NUMERO__</h4>

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
                <button type="button" id="agregar_vivienda" class="btn btn-terceareo">Agregar vivienda</button>
                <button type="button" id="cancelar" class="btn-secundario" data-cerrar-modal>Cancelar</button>
                <button type="submit" id="guardar_usuario" class="btn-primario">Guardar Usuario</button>
            </div>
        </form>

    </div>
</div>

<!--===============================MODAL: VER USUARIO==================================================-->

<div class="modal" id="modal_ver">
    <div class="modal-contenido">

        <span class="cerrar" data-cerrar-modal>✕</span>

        <h3>Información del usuario</h3>

        <!-- Se llena con fetch desde usuario.js (modulos/usuario/ver.php) -->
        <div id="contenido_ver">

        </div>

    </div>
</div>

<!--===============================MODAL: EDITAR USUARIO==================================================-->

<div class="modal" id="modal_editar">
    <div class="modal-contenido">

        <span class="cerrar" data-cerrar-modal>✕</span>

        <!-- Se llena con fetch desde usuario.js (modulos/usuario/editar.php) -->
        <div id="contenido_editar">

        </div>

    </div>
</div>


<!--==================================TABLA PRICNIPAL DE USUARIO==================================================-->

<div class="seccion">
    <table class="tabla_datos">

        <thead class="thead">
            <tr>
                <th>#</th>
                <th>DNI</th>
                <th>NOMBRE</th>
                <th>APELLIDO</th>
                <th>EDAD</th>
                <th>TELEFONO</th>
                <th>CODIGO</th>
                <th>VIVIENDAS</th>
                <th>ACIONES</th>
            </tr>
        </thead>

        <tbody class="tbody">
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u['id_usuario'] ?></td>
                <td><?= $u['dni'] ?></td>
                <td><?= $u['nombre'] ?></td>
                <td><?= $u['apellido'] ?></td>
                <td><?= $u['edad'] ?></td>
                <td><?= $u['telefono'] ?></td>
                <td><?= $u['codigo'] ?></td>
                <td><?= $u['cantidad_viviendas'] ?></td>
                <td>
                    <button class="btn-editar" data-id="<?= $u['id_usuario'] ?>">
                        Editar
                    </button>
                    <a class="btn-eliminar" href="modulos/usuario/eliminar.php?id=<?= $u['id_usuario'] ?>"
                        onclick="return confirm('¿Desea eliminar este usuario?')">
                        Eliminar
                    </a>
                    <button class="btn-ver" data-id="<?= $u['id_usuario'] ?>">
                        Ver
                    </button>
                </td>

            </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>

<!-- Modal: Lista de solicitudes de traspaso pendientes -->
<div class="modal" id="modal-traspasos">
    <div class="modal-contenido" style="width: 720px;">
        <span class="cerrar" data-cerrar-modal>✕</span>
        <div class="formulario">
            <h4>🔄 Solicitudes de traspaso pendientes</h4>

            <?php if (!$traspasos_pendientes): ?>
            <p>No hay traspasos esperando confirmación por ahora.</p>
            <?php else: ?>
            <table class="tabla_datos">
                <thead>
                    <tr>
                        <th>Vivienda</th>
                        <th>Dueño actual</th>
                        <th>Comprador declarado</th>
                        <th>Motivo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($traspasos_pendientes as $t): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($t['numero_vivienda']) ?> (<?= htmlspecialchars($t['nombre_sector'] ?? '—') ?>)</td>
                        <td><?= htmlspecialchars($t['nombre_actual']) ?></td>
                        <td><?= htmlspecialchars($t['nombre_comprador'] . ' ' . $t['apellido_comprador']) ?></td>
                        <td><?= htmlspecialchars($t['motivo']) ?></td>
                        <td>
                            <button type="button" class="btn-editar btn-procesar-traspaso"
                                data-traspaso='<?= htmlspecialchars(json_encode([
                                    'id_solicitud'       => $t['id_solicitud'],
                                    'numero_vivienda'    => $t['numero_vivienda'],
                                    'nombre_sector'      => $t['nombre_sector'],
                                    'nombre_actual'      => $t['nombre_actual'],
                                    'dni_actual'         => $t['dni_actual'],
                                    'nombre_comprador'   => $t['nombre_comprador'],
                                    'apellido_comprador' => $t['apellido_comprador'],
                                    'dni_comprador'      => $t['dni_comprador'],
                                    'telefono_comprador' => $t['telefono_comprador'],
                                    'motivo'             => $t['motivo'],
                                ]), ENT_QUOTES) ?>'>
                                Procesar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Procesar un traspaso -->
<div class="modal" id="modal-procesar-traspaso">
    <div class="modal-contenido" style="width: 560px;">
        <span class="cerrar" data-cerrar-modal>✕</span>
        <div class="formulario">
            <h4>Procesar traspaso</h4>

            <div class="informacion">
                <div class="campo"><label>Vivienda</label><span id="pt-vivienda"></span></div>
                <div class="campo"><label>Dueño actual</label><span id="pt-actual"></span></div>
                <div class="campo"><label>Motivo</label><span id="pt-motivo"></span></div>
            </div>

            <h4 style="margin-top:14px;">Comprador declarado</h4>
            <div class="informacion">
                <div class="campo"><label>Nombre</label><span id="pt-comprador-nombre"></span></div>
                <div class="campo"><label>DNI</label><span id="pt-comprador-dni"></span></div>
                <div class="campo"><label>Teléfono</label><span id="pt-comprador-telefono"></span></div>
            </div>

            <p id="pt-aviso-existente" class="ayuda-revision" style="display:none;">
                Ya existe un usuario registrado con este DNI — se usará esa cuenta como nuevo dueño.
            </p>

            <div id="pt-form-nuevo-usuario" style="display:none;">
                <p class="ayuda-revision">No hay ningún usuario con este DNI todavía. Completa sus datos para crearlo al confirmar:</p>
                <div class="informacion">
                    <div class="campo"><label>Código de acceso</label><input type="text" id="pt-nuevo-codigo" maxlength="50"></div>
                    <div class="campo"><label>Fecha de nacimiento</label><input type="date" id="pt-nuevo-fecha-nacimiento"></div>
                </div>
            </div>

            <form id="form-confirmar-traspaso" style="margin-top:14px;">
                <input type="hidden" id="pt-id-solicitud" name="id_solicitud">
                <input type="hidden" id="pt-codigo-hidden" name="codigo_nuevo_usuario">
                <input type="hidden" id="pt-fecha-hidden" name="fecha_nacimiento_nuevo_usuario">
                <div class="form-acciones">
                    <button type="submit" class="btn-primario">✓ Confirmar traspaso</button>
                </div>
            </form>

            <form id="form-rechazar-traspaso">
                <input type="hidden" id="pt-id-solicitud-rechazar" name="id_solicitud">
                <div class="campo">
                    <label>Motivo del rechazo</label>
                    <input type="text" name="motivo_rechazo" placeholder="Ej: necesitamos verificar identidad en persona" required>
                </div>
                <div class="form-acciones">
                    <button type="submit" class="btn-secundario btn-rechazar">✕ Rechazar</button>
                </div>
            </form>

            <p id="pt-mensaje" class="mensaje-pago"></p>
        </div>
    </div>
</div>