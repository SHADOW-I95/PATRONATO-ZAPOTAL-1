<?php
require_once __DIR__ . "/../../../config/conexion.php";

// Importa el archivo de conexión a la base de datos
require_once __DIR__ . "/../../config/conexion.php";

// Crea la conexión usando la función Connection()

$conexion = Connection();

// Obtiene el número de recibo desde la URL y valida que sea un entero
$numero_recibo = filter_input(INPUT_GET, "recibo", FILTER_VALIDATE_INT);

// Si no es válido, se detiene el script
if (!$numero_recibo) {
    die("Recibo no válido.");
}

// Consulta SQL para obtener los datos principales del pago
$sql = "SELECT pa.id_pago_agua, pa.numero_recibo, pa.fecha_pago_agua, pa.total,
               pa.metodo_pago,
               v.numero_vivienda, v.cuota,
               s.nombre_sector, se.nombre_servicio, ep.nombre_estado_pago,
               CONCAT(u.nombre, ' ', u.apellido) AS nombre_usuario, u.dni
        FROM pagos_agua pa
        INNER JOIN viviendas v ON pa.id_vivienda = v.id_vivienda
        LEFT JOIN sectores s ON v.id_sector = s.id_sector
        LEFT JOIN servicios se ON v.id_servicio = se.id_servicio
        LEFT JOIN estado_pago ep ON v.id_estado_pago = ep.id_estado_pago
        INNER JOIN usuarios u ON pa.id_usuario = u.id_usuario
        WHERE pa.numero_recibo = ?";

// Prepara la consulta para evitar inyección SQL
$stmt = $conexion->prepare($sql);

// Ejecuta la consulta con el número de recibo
$stmt->execute([$numero_recibo]);

// Obtiene el resultado en un arreglo asociativo
$pago = $stmt->fetch();

// Si no se encuentra el recibo, se detiene el script
if (!$pago) {
    die("No se encontró ese recibo.");
}

// Consulta SQL para obtener los meses pagados relacionados al recibo
$sql_meses = "SELECT anio, mes, monto FROM detalle_pago_agua WHERE id_pago_agua = ? ORDER BY anio, mes";
$stmt_meses = $conexion->prepare($sql_meses);
$stmt_meses->execute([$pago['id_pago_agua']]);
$meses_pagados = $stmt_meses->fetchAll();

// Arreglo con nombres de los meses en español
$nombres_meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                   7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Título dinámico con el número de recibo -->
    <title>Factura #<?= sprintf('%06d', $pago['numero_recibo']) ?></title>
    <style>
        /* Estilos generales */
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 14px; color: #000; margin: 0; padding: 30px; }
        .factura { max-width: 650px; margin: 0 auto; border: 1px solid #333; padding: 20px; }

        /* Encabezado con logo y datos */
        .encabezado { display: flex; align-items: center; gap: 15px; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 15px; }
        .encabezado img { width: 70px; height: 70px; object-fit: contain; }
        .encabezado h2 { margin: 0; font-size: 18px; }
        .encabezado p { margin: 2px 0; font-size: 12.5px; }

        /* Título del recibo */
        .titulo-recibo { text-align: center; margin: 15px 0; }
        .titulo-recibo h3 { margin: 0; }

        /* Tabla de datos del pago */
        .datos { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .datos td { padding: 5px 8px; border: 1px solid #ccc; font-size: 13.5px; }
        .datos td:first-child { font-weight: 600; width: 40%; background: #f4f6fa; }

        /* Tabla de meses pagados */
        table.meses { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.meses th, table.meses td { border: 1px solid #ccc; padding: 6px 8px; font-size: 13px; text-align: left; }

        /* Total */
        .total { text-align: right; font-size: 16px; font-weight: 700; margin-top: 10px; }

        /* Firma */
        .div-firma{width: 100%; height: 5px;}
        .firma{display:flex; justify-content:center;}
        .firma-p{font-weight: 700; text-align:center;}

        /* Botón imprimir */
        .btn-imprimir { display: block; margin: 20px auto 0; padding: 10px 20px; }

        /* Estilos para impresión */
        @media print {
            .btn-imprimir { display: none; }
            body { padding: 0; }
            .factura { border: none; }
        }
    </style>
</head>
<body>

    <div class="factura">
        <!-- Encabezado con logo y datos -->
        <div class="encabezado">
            <img src="../../assets/img/logo.png" alt="Logo del Patronato" onerror="this.style.display='none'">
            <div>
                <h2>Patronato Pro-Mejoramiento El Zapotal</h2>
                <p>patronatozapotal@gmail.com</p>
                <p>Teléfono: 9868-3986</p>
                <p>El Zapotal del Norte, Sector Campito, Frente a la Canchita</p>
            </div>
        </div>

        <!-- Título del recibo -->
        <div class="titulo-recibo">
            <h3>Recibo de pago de agua</h3>
            <p>N.° <?= sprintf('%06d', $pago['numero_recibo']) ?></p>
        </div>

        <!-- Tabla con datos del pago -->
        <table class="datos">
            <tr><td>Fecha</td><td><?= htmlspecialchars($pago['fecha_pago_agua']) ?></td></tr>
            <tr><td>Usuario</td><td><?= htmlspecialchars($pago['nombre_usuario']) ?></td></tr>
            <tr><td>DNI</td><td><?= htmlspecialchars($pago['dni']) ?></td></tr>
            <tr><td>Vivienda</td><td>#<?= htmlspecialchars($pago['numero_vivienda']) ?></td></tr>
            <tr><td>Sector</td><td><?= htmlspecialchars($pago['nombre_sector'] ?? '—') ?></td></tr>
            <tr><td>Tipo de servicio</td><td><?= htmlspecialchars($pago['nombre_servicio'] ?? '—') ?></td></tr>
            <tr><td>Cuota mensual</td><td>L<?= number_format($pago['cuota'], 2) ?></td></tr>
            <tr><td>Método de pago</td><td><?= htmlspecialchars($pago['metodo_pago'] ?? 'Efectivo') ?></td></tr>
            <tr><td>Estado del pago</td><td><?= htmlspecialchars($pago['nombre_estado_pago'] ?? '—') ?></td></tr>
        </table>

        <!-- Tabla con meses pagados -->
        <table class="meses">
            <thead>
                <tr><th>Mes pagado</th><th>Año</th><th>Monto (L)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($meses_pagados as $m): ?>
                <tr>
                    <td><?= $nombres_meses[(int) $m['mes']] ?? $m['mes'] ?></td>
                    <td><?= $m['anio'] ?></td>
                    <td>L<?= number_format($m['monto'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Total -->
        <p class="total">Total pagado: L<?= number_format($pago['total'], 2) ?></p>

        <!-- Firma -->
        <div class="div-firma">
            <span class="firma">______________________________</span>
            <p class="firma-p">firma</p>
        </div>

        <!-- Botón imprimir -->
        <button class="btn-imprimir" onclick="window.print()">Imprimir factura</button>
    </div>

</body>
</html>
