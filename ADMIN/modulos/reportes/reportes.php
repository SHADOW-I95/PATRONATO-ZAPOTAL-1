<div class="contenido">

  <div class="encabezado">
    <h1>.Reportes</h1>
  </div>

  <div class="algodenose">
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
        <a href="index.php" class="btn-limpiar">Limpiar</a>
        <button type="submit" class="btn-filtrar">Filtrar</button>
        <a href="nuevo.php" class="btn-nuevo">+ Nuevo Reporte</a>
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
        $conn = connection();

        // 🔹 Lógica de filtros
        $where = [];

        if (!empty($_GET['tipo'])) {
            $where[] = "tipo = '".$_GET['tipo']."'";
        }
        if (!empty($_GET['estado'])) {
            $where[] = "estado = '".$_GET['estado']."'";
        }
        if (!empty($_GET['fecha_desde'])) {
            $where[] = "fecha >= '".$_GET['fecha_desde']."'";
        }
        if (!empty($_GET['fecha_hasta'])) {
            $where[] = "fecha <= '".$_GET['fecha_hasta']."'";
        }
        if (!empty($_GET['buscar'])) {
            $buscar = $_GET['buscar'];
            $where[] = "(asunto LIKE '%$buscar%' OR descripcion LIKE '%$buscar%')";
        }

        $sql = "SELECT * FROM reportes";
        if (count($where) > 0) {
            $sql .= " WHERE ".implode(" AND ", $where);
        }

        $result = mysqli_query($conn, $sql);
        include("../conexion.php");
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
                    <a href='editar.php?dni=".$row['dni']."' class='btn-editar'>Editar</a>
                    <a href='eliminar.php?dni=".$row['dni']."' class='btn-eliminar'
                       onclick=\"return confirm('¿Seguro que deseas eliminar este reporte?');\">Eliminar</a>
                  </td>";
            echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
