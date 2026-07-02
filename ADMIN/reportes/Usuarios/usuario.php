 <div class="contenido">

 <div class="encabezado">
            <h1>.Reportes</h1>
</div>

<dic class="algodenose">
            <h3>Inicio / Reportes</h3>
</div>

<div class="bloque-filtros">
<form method="GET" action="" class="filtros">
  <div class="fila-filtros">
    <div class="reportes">
      <label>Tipo de Reportes:</label>
      <select name="tipo">
        <option value="">Todos</option>
        <option value="Agua">Agua</option>
        <option value="Luz">Luz</option>
        <option value="Otro">Otro</option>
      </select>
    </div>

    <div class="estado">
      <label>Estado:</label>
      <select name="estado">
        <option value="">Todos</option>
        <option value="EN PROCESO">En Proceso</option>
        <option value="FINALIZADO">Finalizado</option>
      </select>
    </div>

    <div class="desde">
      <label>Fecha desde:</label>
      <input type="date" name="fecha_desde">
    </div>

    <div class="hasta">
      <label>Fecha hasta:</label>
      <input type="date" name="fecha_hasta">
    </div>  

    <div class="buscar">  
      <label>Buscar:</label>
      <input type="text" name="buscar" placeholder="Buscar reporte...">
    </div>
  </div>

  <div class="fila-botones">
    <a href="Usuarios/reportes.php" class="btn-limpiar">Limpiar</a>
    <button type="submit" class="btn-filtrar">Filtrar</button>
    <button type="button" class="btn-nuevo">+ Nuevo Reporte</button>
  </div>
</form>

</div>

    <div class="div-table">
    <table class="table">
        <thead class="thead">
            <tr>
                <th>#</th>
                <th>DNI</th>
                <th>Usuario</th>
                <th>Asunto</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody class="tbody">
            <?php
            include("conexion.php");
            $sql = "SELECT * FROM reportes";
            $result = mysqli_query($conn, $sql);
            $contador = 1;

            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>".$contador++."</td>";
                echo "<td>".$row['dni']."</td>";
                echo "<td>".$row['usuario']."</td>";
                echo "<td>".$row['asunto']."</td>";
                echo "<td>".$row['descripcion']."</td>";
                echo "<td>".$row['fecha']."</td>";
                echo "<td>".$row['estado']."</td>";
                echo "<td>
                
                      </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
