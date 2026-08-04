<?php
require_once __DIR__ . "/../../config/conexion.php";
$conexion = Connection();
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

<!--===============================MODAL DE AGREGAR NUEVO USUARIO-SE ENCUENTRA EN PROCESO==================================================-->

<div class="modal" id="modal">
    <div class="modal-contenido">

        <span class="cerrar" id="cerrar-modal">✕</span>
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
            </div>


            <div class="form-acciones">
                <button type="button" id="agregar_vivienda" class="btn btn-terceareo">Agregar vivienda</button>
                <button type="button" id="cancelar" class="btn-secundario">Cancelar</button>
                <button type="submit" id="guardar_usuario" class="btn-primario">Guardar Usuario</button>
            </div>
        </form>

    </div>
</div>

<div class="modal" id="modal_ver">
    <div class="modal-contenido">

        <span class="cerrar" id="cerrar_ver">✕</span>

        <h3>Información del usuario</h3>

        <div id="contenido_ver">

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
                    <a class="btn-editar" href="modulos/usuario/editar.php?id=<?= $u['id_usuario'] ?>">
                        Editar
                    </a>
                    <a class="btn-eliminar" href="modulos/usuario/eliminar.php?id=<?= $u['id_usuario'] ?>"
                        onclick="return confirm('¿Desea eliminar este usuario?')">
                        Eliminar
                    </a>
                    <button class="btn-ver btn_ver" data-id="<?= $u['id_usuario'] ?>">
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
viviendaOriginal = contenedor.innerHTML;
let indice = 1;
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

                <?php foreach ($sectores as $s): ?>
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

    indice++;
});

function reiniciarFormulario() {
    formulario.reset();
    contenedor.innerHTML = viviendaOriginal;
    indice = 1;
}

const btnCancelar = document.getElementById("cancelar");

btnCancelar.addEventListener("click", () => {
    modal.style.display = "none";

    reiniciarFormulario();

});

const modal = document.getElementById("modal");
const cerrar = document.getElementById("cerrar-modal");
const abrir = document.getElementById("abrir-modal");


abrir.addEventListener("click", () => {
    modal.style.display = "flex";
});

cerrar.addEventListener("click", () =>{
    modal.style.display = "none";
});

window.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

// ==================== Botón "Ver" del usuario ====================
const modalVer = document.getElementById("modal_ver");
const cerrarVer = document.getElementById("cerrar_ver");
const contenidoVer = document.getElementById("contenido_ver");

document.querySelectorAll(".btn_ver").forEach((boton) => {
    boton.addEventListener("click", () => {
        const idUsuario = boton.getAttribute("data-id");

        fetch("modulos/usuario/ver.php?id=" + idUsuario)
            .then((respuesta) => respuesta.text())
            .then((html) => {
                contenidoVer.innerHTML = html;
                modalVer.style.display = "flex";
            });
    });
});

cerrarVer.addEventListener("click", () => {
    modalVer.style.display = "none";
});

window.addEventListener("click", (e) => {
    if (e.target === modalVer) {
        modalVer.style.display = "none";
    }
});
</script>