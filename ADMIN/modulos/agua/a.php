<?php
require_once __DIR__ . "/../../config/conexion.php";
$conexion = Connection();

// Lista de usuarios para el select
$sql_usuarios = 
   "SELECT 
      id_usuario,
    CONCAT(nombre, ' ', apellido) AS nombre_completo,
      dni
    FROM usuarios
    ORDER BY nombre";
$stmt_usuarios = $conexion->prepare($sql_usuarios);
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->fetchAll();

// Si ya eligieron un usuario, traemos sus viviendas
$id_usuario_seleccionado = filter_input(INPUT_GET, "id_usuario", FILTER_VALIDATE_INT);
$viviendas = [];

if ($id_usuario_seleccionado) {
    $sql_viviendas = 
        "SELECT 
            v.id_vivienda,
            v.numero_vivienda,
            v.cuota,
            s.nombre_sector,
            se.nombre_servicio,
            ep.nombre_estado_pago
        FROM viviendas v
        LEFT JOIN sectores s ON v.id_sector = s.id_sector
        LEFT JOIN servicios se ON v.id_servicio = se.id_servicio
        LEFT JOIN estado_pago ep ON v.id_estado_pago = ep.id_estado_pago
        WHERE v.id_usuario = ?
    ";
    $stmt_viviendas = $conexion->prepare($sql_viviendas);
    $stmt_viviendas->execute([$id_usuario_seleccionado]);
    $viviendas = $stmt_viviendas->fetchAll();
}
?>

<div class="modulo_header">
    <div class="encabezado">
        <h1>Historial de pagos de agua</h1>
    </div>
    <div class="opciones">
        <input type="text" placeholder="Nombre o DNI..." class="buscar">
    </div>
</div>

<div class="formulario-pago">
    <h4>Registrar pago</h4>

    <!-- Paso 1: elegir usuario. Al enviar, recarga la página con ?id_usuario=X -->
<form method="GET" class="formulario">
    <input type="hidden" name="modulo" value="agua">

    <div class="campo">
        <label>Usuario</label>
        <select name="id_usuario" required>
            <option value="">Selecciona…</option>
            <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id_usuario'] ?>"
                <?= ($id_usuario_seleccionado == $u['id_usuario']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['nombre_completo']) ?> (<?= htmlspecialchars($u['dni']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn-secundario">Buscar viviendas</button>
</form>
    <!-- Paso 2: si hay usuario seleccionado, mostramos sus viviendas y el form de pago -->
    <?php if ($id_usuario_seleccionado): ?>

        <?php if (empty($viviendas)): ?>
            <p class="mensaje_vacio">Este usuario no tiene viviendas registradas.</p>
        <?php else: ?>

            <form action="modulos/agua/registrar_pago.php" method="POST" class="formulario">
                <input type="hidden" name="id_usuario" value="<?= $id_usuario_seleccionado ?>">

                <div class="campo">
                    <label>Vivienda a pagar</label>
                    <select name="id_vivienda" required>
                        <option value="">Selecciona…</option>
                        <?php foreach ($viviendas as $v): ?>
                        <option value="<?= $v['id_vivienda'] ?>">
                            #<?= htmlspecialchars($v['numero_vivienda']) ?> —
                            <?= htmlspecialchars($v['nombre_sector']) ?> —
                            L<?= htmlspecialchars($v['cuota']) ?> —
                            <?= htmlspecialchars($v['nombre_estado_pago'] ?? 'Sin estado') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label>Mes que se paga</label>
                    <input type="month" name="mes_pagado" required>
                </div>

                <div class="campo">
                    <label>Monto pagado (L)</label>
                    <input type="number" step="0.01" min="0" name="monto" required>
                </div>

                <div class="form-acciones">
                    <button type="submit" class="btn-primario">Guardar Pago</button>
                </div>
            </form>

        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Aquí después ponemos la tabla de historial de pagos -->