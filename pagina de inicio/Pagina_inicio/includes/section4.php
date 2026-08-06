<?php

if(isset($_POST['enviar'])){

    $tipo = $_POST['tipo_reporte'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO reportes (id_tipo_reporte, descripcion)
            VALUES ('$tipo', '$descripcion')";

    if(mysqli_query($conexion, $sql)){
        echo "<script>alert('Reporte guardado correctamente');</script>";
    }else{
        echo "Error: " . mysqli_error($conexion);
    }
}
?>

<div class="div4">
    <div class="contenedor">
        <h2>Reportar Problema</h2>

        <form method="POST">
            <label>Tipo de Reporte</label>
            <select name="tipo_reporte" required>
                <option value="">Seleccione una opción</option>
                <option value="1">Fuga de Agua</option>
                <option value="2">Falta de Agua</option>
                <option value="3">Baja Presión</option>
                <option value="4">Daño de Tubería</option>
                <option value="5">Otro</option>
            </select>

            <label>Descripción</label>
            <textarea name="descripcion" rows="5" placeholder="Describa el problema..." required></textarea>

            <button type="submit" name="enviar">Enviar Reporte</button>
        </form>
    </div>
</div>