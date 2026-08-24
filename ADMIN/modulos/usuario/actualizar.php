<?php
require_once __DIR__ . "/../../../config/conexion.php";
require_once __DIR__ . "/../../../config/permisos.php";
require_once __DIR__ . "/../../../config/bitacora.php";
$conexion = Connection();

header('Content-Type: application/json; charset=utf-8');
// IMPORTANTE: este archivo se llama por fetch() desde usuario.js, que espera
// una respuesta JSON (igual que agregar.php). Antes este archivo respondía
// con header('Location: ...') o con die("texto plano"), que usuario.js no
// podía interpretar como JSON — la promesa fallaba en silencio y el botón
// "Guardar cambios" del modal de Editar parecía no hacer nada.

if (!tienePermiso('usuario')) {
    http_response_code(403);
    echo json_encode(["ok" => false, "error" => "sin_permiso"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["ok" => false, "error" => "metodo_invalido"]);
    exit;
}

$id_usuario = (int) ($_POST["id_usuario"] ?? 0);

if (!$id_usuario) {
    echo json_encode(["ok" => false, "error" => "usuario_invalido"]);
    exit;
}

try {

    // Todo lo de abajo se guarda junto: si algo falla, no se queda a medias
    $conexion->beginTransaction();

    // =======================
    // DATOS DEL USUARIO
    // =======================

    $dni              = trim($_POST["DNI"]);
    $codigo           = trim($_POST["codigo"]);
    $nombre           = trim($_POST["nombre"]);
    $apellido         = trim($_POST["apellido"]);
    $fecha_nacimiento = !empty($_POST["fecha_nacimiento"]) ? $_POST["fecha_nacimiento"] : null;
    $telefono         = !empty($_POST["telefono"]) ? $_POST["telefono"] : null;

    $sqlUsuario = "
        UPDATE usuarios SET
            dni = ?,
            codigo = ?,
            nombre = ?,
            apellido = ?,
            fecha_nacimiento = ?,
            telefono = ?
        WHERE id_usuario = ?";

    $stmtUsuario = $conexion->prepare($sqlUsuario);

    // Actualiza los datos básicos del usuario
    $stmtUsuario->execute([
        $dni,
        $codigo,
        $nombre,
        $apellido,
        $fecha_nacimiento,
        $telefono,
        $id_usuario
    ]);

    // =======================
    // VIVIENDAS QUE SE QUITARON EN EL MODAL
    // =======================

    if (!empty($_POST["viviendas_eliminar"])) {

        // Llega como "3,7,10" desde el campo oculto que llena usuario.js
        $ids_eliminar = array_filter(array_map('intval', explode(',', $_POST["viviendas_eliminar"])));

        if ($ids_eliminar) {
            // Un "?" por cada id, para poder usar el arreglo directo en execute()
            $marcadores = implode(',', array_fill(0, count($ids_eliminar), '?'));
            $stmtEliminar = $conexion->prepare("DELETE FROM viviendas WHERE id_vivienda IN ($marcadores)");
            $stmtEliminar->execute($ids_eliminar);
        }
    }

    // =======================
    // VIVIENDAS NUEVAS O EDITADAS
    // =======================

    if (!empty($_POST["vivienda"])) {

        $sqlActualizar = "
            UPDATE viviendas SET
                id_sector = ?,
                id_servicio = ?,
                numero_vivienda = ?,
                cuota = ?,
                id_estado_pago = ?
            WHERE id_vivienda = ?";
        $stmtActualizar = $conexion->prepare($sqlActualizar);

        $sqlNueva = "
            INSERT INTO viviendas
            (id_usuario, id_sector, id_servicio, numero_vivienda, cuota, id_estado_pago)
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmtNueva = $conexion->prepare($sqlNueva);

        // Recorre cada vivienda que llegó del formulario: la actualiza si ya existía, o la crea si es nueva
        foreach ($_POST["vivienda"] as $v) {

            if (!empty($v["id"])) {
                // Trae "id", así que ya existía: se actualiza
                $stmtActualizar->execute([
                    $v["sector"],
                    $v["servicio"],
                    $v["numero"],
                    $v["cuota"],
                    $v["estado"],
                    $v["id"]
                ]);
            } else {
                // No trae "id": se agregó nueva desde el botón "Agregar vivienda"
                $stmtNueva->execute([
                    $id_usuario,
                    $v["sector"],
                    $v["servicio"],
                    $v["numero"],
                    $v["cuota"],
                    $v["estado"]
                ]);
            }
        }
    }

    // Todo salió bien: se confirman los cambios
    $conexion->commit();

    registrar_actividad('usuario', 'editó', "Editó al usuario {$nombre} {$apellido} (DNI {$dni})");

    echo json_encode(["ok" => true]);
    exit;

} catch (PDOException $e) {

    // Algo falló: se deshace todo lo que se haya intentado guardar en esta transacción
    $conexion->rollBack();

    if ($e->getCode() == 23000) {
        echo json_encode(["ok" => false, "error" => "dato_duplicado"]);
        exit;
    }

    echo json_encode(["ok" => false, "error" => "error_guardando"]);
    exit;
}
