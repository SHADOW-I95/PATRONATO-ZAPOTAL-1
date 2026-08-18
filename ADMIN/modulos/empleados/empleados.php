<?php
require_once __DIR__ . "/../../../config/conexion.php";
$conexion = Connection();

// Lista de empleados, con su edad calculada igual que en el módulo de usuarios
$sql_empleados = "SELECT
  id_empleado,
  dni,
  nombre,
  apellido,
  telefono,
  codigo,
  TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad
  FROM empleados
  ORDER BY nombre";
$stmt_empleados = $conexion->prepare($sql_empleados);
$stmt_empleados->execute();
$empleados = $stmt_empleados->fetchAll();
?>

<div class="modulo_header">
    <div class="encabezado">
        <h1>Empleados</h1>
    </div>
    <div class="opciones">
        <input type="text" placeholder="Nombre o DNI..." class="buscar">
        <button class="btn_nuevo" id="abrir-modal">
            + Nuevo Empleado
        </button>
    </div>
</div>

<!--===============================MODAL: NUEVO EMPLEADO==================================================-->

<div class="modal" id="modal">
    <div class="modal-contenido">

        <span class="cerrar" data-cerrar-modal>✕</span>
        <h4>+ Nuevo empleado</h4>
        <form action="modulos/empleados/agregar.php" method="POST" class="formulario" id="form_empleado">
            <div class="informacion">

                <div class="campo">
                    <label>DNI</label>
                    <input type="text" name="DNI" required maxlength="20" placeholder="0801199912345">
                </div>
                <div class="campo">
                    <label>Código de acceso</label>
                    <input type="text" name="codigo" required maxlength="50" placeholder="Código para iniciar sesión">
                </div>
                <div class="campo">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required maxlength="30">
                </div>
                <div class="campo">
                    <label>Apellido</label>
                    <input type="text" name="apellido" required maxlength="30">
                </div>
                <div class="campo">
                    <label>Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento">
                </div>
                <div class="campo">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" maxlength="30">
                </div>
            </div>

            <div class="form-acciones">
                <button type="button" class="btn-secundario" data-cerrar-modal>Cancelar</button>
                <button type="submit" class="btn-primario">Guardar Empleado</button>
            </div>
        </form>

    </div>
</div>

<!--===============================MODAL: VER EMPLEADO==================================================-->

<div class="modal" id="modal_ver">
    <div class="modal-contenido">

        <span class="cerrar" data-cerrar-modal>✕</span>

        <div id="contenido_ver">
            <!-- Se llena con fetch desde empleados.js (modulos/empleados/ver.php) -->
        </div>

    </div>
</div>

<!--===============================MODAL: EDITAR EMPLEADO==================================================-->

<div class="modal" id="modal_editar">
    <div class="modal-contenido">

        <span class="cerrar" data-cerrar-modal>✕</span>

        <div id="contenido_editar">
            <!-- Se llena con fetch desde empleados.js (modulos/empleados/editar.php) -->
        </div>

    </div>
</div>

<!--==================================TABLA PRINCIPAL DE EMPLEADOS==================================================-->

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
                <th>ACCIONES</th>
            </tr>
        </thead>

        <tbody class="tbody">
            <?php foreach ($empleados as $e): ?>
            <tr>
                <td><?= $e['id_empleado'] ?></td>
                <td><?= htmlspecialchars($e['dni']) ?></td>
                <td><?= htmlspecialchars($e['nombre']) ?></td>
                <td><?= htmlspecialchars($e['apellido']) ?></td>
                <td><?= htmlspecialchars($e['edad'] ?? '—') ?></td>
                <td><?= htmlspecialchars($e['telefono'] ?? '—') ?></td>
                <td><?= htmlspecialchars($e['codigo']) ?></td>
                <td>
                    <button class="btn-editar" data-id="<?= $e['id_empleado'] ?>">
                        Editar
                    </button>
                    <a class="btn-eliminar" href="modulos/empleados/eliminar.php?id=<?= $e['id_empleado'] ?>"
                        onclick="return confirm('¿Desea eliminar este empleado?')">
                        Eliminar
                    </a>
                    <button class="btn-ver" data-id="<?= $e['id_empleado'] ?>">
                        Ver
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>