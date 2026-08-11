<?php
require_once __DIR__ . "/../../config/conexion.php";
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
    </div>
</div>

<!--===============================MODAL: NUEVO USUARIO==================================================-->

<div class="modal" id="modal">
    <div class="modal-contenido">

        <!-- data-cerrar-modal: lo cierra modal.js, sin necesitar un id específico -->
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

            <!-- Vivienda inicial (índice 0). Las que se agreguen con el botón "Agregar vivienda" siguen este mismo patrón -->
            <div id="contenedor_viviendas">
                <div class=" vivienda">
                    <div class="campo">
                        <label>Vivienda </label>
                        <input type="text" name="vivienda[0][numero]" placeholder="Numero de vivienda">
                    </div>

                    <div class="campo">
                        <label>Sector </label>
                        <select name="vivienda[0][sector]" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($sectores as $s): // Un <option> por cada sector de la base de datos ?>
                            <option value="<?= $s['id_sector'] ?>"><?= htmlspecialchars($s['nombre_sector']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label>tipo servicio</label>
                        <select name="vivienda[0][servicio]" required>
                            <option value="">Selecion…</option>
                            <?php foreach ($servicios as $s): // Un <option> por cada tipo de servicio ?>
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
                            <?php foreach ($estado_pago as $estado): // Un <option> por cada estado (Pagado/Pendiente/Mora) ?>
                            <option value="<?= $estado['id_estado_pago']?>">
                                <?= htmlspecialchars($estado['nombre_estado_pago'])?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                </div>
            </div>

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
            <?php foreach ($usuarios as $u): // Una fila por cada usuario que trajo la consulta ?>
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
                    <!-- data-id: usuario.js lo usa para saber a quién editar -->
                    <button class="btn-editar" data-id="<?= $u['id_usuario'] ?>">
                        Editar
                    </button>
                    <a class="btn-eliminar" href="modulos/usuario/eliminar.php?id=<?= $u['id_usuario'] ?>"
                        onclick="return confirm('¿Desea eliminar este usuario?')">
                        Eliminar
                    </a>
                    <!-- data-id: usuario.js lo usa para saber a quién mostrar -->
                    <button class="btn-ver" data-id="<?= $u['id_usuario'] ?>">
                        Ver
                    </button>
                </td>

            </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>


<!--==========================    ESTA WEAA ES DEL COSO PARA AGREGAR MAS VIVIENDAS YA QUE <?PHP?> NO SIRVE DENTRO DE JS ==============================================-->

<script>
const formulario = document.getElementById("form_usuario");
const contenedor = document.getElementById("contenedor_viviendas");
const btnAgregar = document.getElementById("agregar_vivienda");
viviendaOriginal = contenedor.innerHTML; // copia del HTML original, para poder restaurarlo al cancelar
let indice = 1; // arranca en 1 porque la vivienda 0 ya viene en el HTML

// Al presionar "Agregar vivienda", crea una fila nueva con su propio índice
btnAgregar.addEventListener("click", () => {

    const vivienda = document.createElement("div");
    vivienda.classList.add("vivienda");

    vivienda.innerHTML = `

        <hr>

        <h4>Vivienda ${indice + 1}</h4>

        <div class="campo">
            <label>Vivienda</label>
            <input type="text"
                   name="vivienda[${indice}][numero]"
                   placeholder="Número de vivienda">
        </div>

        <div class="campo">
            <label>Sector</label>
            <select name="vivienda[${indice}][sector]" required>
                <option value="">Selecciona…</option>

                <?php foreach ($sectores as $s): // Mismas opciones que la vivienda 0, generadas por PHP ?>
                    <option value="<?= $s['id_sector'] ?>">
                        <?= htmlspecialchars($s['nombre_sector']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="campo">
            <label>Tipo servicio</label>
            <select name="vivienda[${indice}][servicio]" required>

                <option value="">Selecciona…</option>

                <?php foreach ($servicios as $s): ?>
                    <option value="<?= $s['id_servicio'] ?>">
                        <?= htmlspecialchars($s['nombre_servicio']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="campo">
            <label>Cuota mensual (L)</label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="vivienda[${indice}][cuota]"
                   value="0">
        </div>

        <div class="campo">
            <label>Estado</label>
            <select name="vivienda[${indice}][estado]">
               <option value="">Selecion…</option>
                <?php foreach ($estado_pago as $estado):?>
                    <option value="<?= $estado['id_estado_pago']?>">
                    <?= htmlspecialchars($estado['nombre_estado_pago'])?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

    `;

    contenedor.appendChild(vivienda);

    indice++; // el siguiente "Agregar vivienda" usará el número que sigue
});

// Deja el formulario como estaba al abrirlo (quita las viviendas que se hayan agregado)
function reiniciarFormulario() {
    formulario.reset();
    contenedor.innerHTML = viviendaOriginal;
    indice = 1;
}

const btnCancelar = document.getElementById("cancelar");

// El cierre del modal ya lo hace data-cerrar-modal (modal.js); aquí solo se limpia el formulario
btnCancelar.addEventListener("click", () => {
    reiniciarFormulario();
});
</script>