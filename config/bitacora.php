<?php
/**
 * Bitácora de actividad de empleados.
 *
 * Uso: una sola línea en cualquier archivo que crea, edita o elimina algo:
 *
 *   registrar_actividad('usuario', 'creó', "Registró al usuario {$nombre} (DNI {$dni})");
 *
 * $modulo  -> a qué parte del sistema pertenece: usuario, agua, empleados,
 *             reportes, mapa, configuracion (para poder filtrar después).
 * $accion  -> 'creó', 'editó' o 'eliminó' (verbo corto, en pasado).
 * $detalle -> frase legible de qué pasó exactamente. Debe incluir datos
 *             que identifiquen el registro afectado (nombre, número de
 *             vivienda, folio, etc.), no solo un id numérico, para que
 *             se entienda sin tener que ir a buscar nada más.
 *
 * No se registran acciones de solo lectura (ver una lista, abrir un
 * módulo) — eso generaría demasiado ruido sin aportar nada para rendir
 * cuentas. Solo se llama esta función cuando algo realmente cambió.
 */
function registrar_actividad(string $modulo, string $accion, string $detalle): void
{
    if (!isset($_SESSION['id']) || ($_SESSION['tipo'] ?? '') !== 'empleado') {
        return; // solo se registran acciones de empleados con sesión activa
    }

    try {
        $conexion = Connection();
        $stmt = $conexion->prepare(
            "INSERT INTO bitacora (id_empleado, modulo, accion, descripcion) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([(int) $_SESSION['id'], $modulo, $accion, $detalle]);
    } catch (Throwable $e) {
        // Si la bitácora falla por lo que sea, nunca debe tumbar la acción
        // real (guardar el pago, el usuario, etc.) que sí importa que pase.
    }
}