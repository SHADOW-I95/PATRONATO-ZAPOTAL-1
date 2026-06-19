<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crud PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/b541b1b541.js" crossorigin="anonymous"></script>
</head>
<body>
    <h1 class="text-center p-3">Crud PHP</h1>

    <!-- Botón para abrir el modal -->
  <div class="mb-3">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registroModal">
      Registrar Usuario
    </button>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="registroModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        
        <!-- Encabezado del modal -->
        <div class="modal-header">
          <h5 class="modal-title" id="registroModalLabel">Registro de Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

<!-- Cuerpo del modal con el formulario -->
        <div class="modal-body">
          <form method="POST" action="agregar.php">
            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" name="nombre" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Apellido</label>
              <input type="text" class="form-control" name="apellido" required>
            </div>
            <div class="mb-3">
              <label class="form-label">ID</label>
              <input type="text" class="form-control" name="id" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Fecha de Nacimiento</label>
              <input type="date" class="form-control" name="fecha_nacimiento" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Correo Gmail</label>
              <input type="email" class="form-control" name="gmail" required>
            </div>
            <button type="submit" class="btn btn-primary" name="Registrar" value="ok">Registrar</button>
          </form>
        </div>
        
        <!-- Pie del modal -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

<table class="table">
  <thead class="table-primary">
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Nombre</th>
      <th scope="col">Apellido</th>
      <th scope="col">Fecha de Nacimiento</th>
      <th scope="col">Correo</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
      <td>1990-01-01</td>
      <td>@mdo</td>
      <td>
        <a href="" class="btn btn-warning"><i class="fa-solid fa-pen-to-square"></i></a>
        <a href="" class="btn btn-danger"><i class="fa-solid fa-trash"></i></a>
      </td>
    </tr>
  </tbody>
</table>
    
  
</form>
    </div>

    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>