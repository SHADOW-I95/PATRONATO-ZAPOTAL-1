<?php
  // Incluye el archivo de conexión a la base de datos
  require_once "./config/conexion.php";

  // Consulta que trae todos los usuarios registrados en la tabla "usuarios"
  $sql = "SELECT * FROM usuarios";
  $conexion = connection();
  $query = mysqli_query($conexion, $sql);
?>

<!--===============================boton de agregar nuevo usuario y barra de busqueda
                          solo esta la fachada falta darle funcionalidad a la barra de busqueda=================================-->

 <div class="contenido">

        <!-- Encabezado principal de la página -->
        <div class="encabezado">
            <h1>Usuarios</h1>
        </div>

        <!-- Barra de búsqueda (input) y botón para abrir el modal de nuevo usuario -->
        <div class="buscador">
            <input type="text" placeholder="Buscar usuario...">

            <!-- Este botón activa el modal mediante JS (ver id "abrir-modal") -->
            <button class="btn-nuevo" id="abrir-modal">
                + Nuevo Usuario
            </button>

        </div>
    </div>

<!--===============================MODAL DE AGREGAR NUEVO USUARIO-SE ENCUENTRA EN PROCESO==================================================-->

    <!-- Modal (ventana emergente) que contiene el formulario para agregar un nuevo usuario -->
    <div class="modal" id="modal">
        <div class="modal-contenido">

            <!-- Botón "X" para cerrar el modal -->
            <span class="cerrar" id="cerrar-modal">&times;</span>
            <h2>Nuevo usuario</h2>

            <!-- Formulario que envía los datos por POST a agregar.php -->
            <!-- Se usa ruta absoluta para evitar errores 404 si esta página se carga dinámicamente dentro de otra -->
            <form  action="/PATRONATO-ZAPOTAL-1/ADMIN/Usuarios/agregar.php" method="POST">

                <!-- Sección 1: Datos personales del usuario -->
                <div class="informacion_personal">
                    <span>infromacion personal</span>

                    <label for="">DNI</label>
                    <input type="number" placeholder="Numero identidad" name="dni" >

                    <label for="">Nombre</label>
                    <input type="text" placeholder="Nombre" name="nombre">

                    <label for="">Apellido</label>
                    <input type="text" placeholder="Apellido" name="apellido">

                    <label for="">Fecha de Nacimiento</label>
                    <input type="date" placeholder="dd/mm/aa" name="fecha_nac">

                    <label for="">Telefono</label>
                    <input type="number" placeholder="Telefono" name="telefono">

                    <label for="">Correo</label>
                    <input type="email" placeholder="Email" name="email">
                </div>

                <!-- Sección 2: Datos del servicio contratado -->
                <div class="inormacion_servicios">
                    <span>Servivios</span>

                    <label for="">Sector</label>
                    <!-- Lista desplegable con los sectores disponibles -->
                    <select id="" name="sector">
                        <option>Aida</option>
                        <option>Balvino</option>
                        <option>Brisas del Rio</option>
                        <option>Camino la coronilla</option>
                        <option>Campita</option>
                        <option>Campo de Futbol</option>
                        <option>Calle las Delicias</option>
                        <option>Calle Principal</option>
                        <option>Colegio</option>
                        <option>Chupin</option>
                        <option>Denis Montes</option>
                        <option>Mangal</option>
                        <option>Mario Claros</option>
                        <option>Oviedo</option>
                        <option>Pedregal</option>
                        <option>Tabora</option>
                        <option>Vueltona</option>
                    </select>

                    <label for="">Numero de Casa</label>
                    <input type="number" placeholder="Numero casa" name="numero_casa">

                    <label for="">Tipo Servicio</label>
                    <!-- Nota: en la BD el ENUM solo acepta 'CASA', 'APARTAMENTO', 'NEGOCIO' -->
                    <!-- La opción "Alquilado" no existe en ese ENUM, causaría error al insertar -->
                    <select id="" name="tipo_servicio">
                        <option>Casa</option>
                        <option>Apartamento</option>
                        <option>Negocio</optio>  <!-- Etiqueta de cierre mal escrita: debería ser </option> -->
                        <option>Alquilado</option>
                    </select>

                    <label for="">Cantidad Propiedades</label>
                    <input type="number" placeholder="Cntidad de propiedades" name="cant_propiedades">

                    <label for="">Cuota Mensual</label>
                    <!-- Nota: el name aquí es "cuota", pero en agregar.php se espera "cuota_mensual" según la tabla; revisar coincidencia -->
                    <input type="number" placeholder="Cuota Mensual" name="cuota">

                    <label for="">Estado</label>
                    <select id="" name="estado">
                        <option>Activo</option>
                        <option>Inactivo</option>
                    </select>

                    <label for="">Obervaciones</label>
                    <input type="text" placeholder="Observaciones" name="observaciones">

                </div>

                <!-- Botón para enviar el formulario -->
                 <input type="submit">
            </form>

        </div>
    </div>


<!--==================================TABLA DONDE SE GUARDARA ALGUNA INFORMACION DE USUARIOS==================================================-->

    <!-- Tabla que muestra la lista de usuarios registrados en la base de datos -->
    <div class="div-table">
        <table class="table">

            <!-- Encabezados de las columnas de la tabla -->
            <thead class="thead">
                <tr>
                    <th>#</th>
                    <th>DNI</th>
                    <th>NOMBRE</th>
                    <th>APELLIDO</th>
                    <th>TELEFONO</th>
                    <th>SECTOR</th>
                    <th>NUMERO_CASA</th>
                    <th>TIPO_SERVICIO</th>
                    <th>ESTADO</th>

                    <th>EDITAR</th>
                    <th>ELIMINAR</th>
                    <th>VER</th>
                </tr>
            </thead>

<!--==========================LA LOGICA=este es el cuerpo de la pagina donde se guarda la informacion que haya en la base de datos=======================================================-->

            <tbody class="tbody">
              <?php
                // Recorre cada fila (usuario) obtenida de la consulta SQL
                while($row = mysqli_fetch_array($query)): ?>
                <tr>
                    <!-- Se imprime cada campo del usuario actual -->
                    <th><?= $row['id_usuario'] ?></th>
                    <th><?= $row['DNI'] ?></th>
                    <th><?= $row['NOMBRE'] ?></th>
                    <th><?= $row['APELLIDO'] ?></th>
                    <th><?= $row['TELEFONO'] ?></th>
                    <th><?= $row['SECTOR'] ?></th>
                    <th><?= $row['NUMERO_CASA'] ?></th>
                    <th><?= $row['TIPO_SERVICIO'] ?></th>

                    <!-- La clase CSS cambia según el estado (activo/inactivo) para darle color distinto -->
                    <th class="estado <?= strtolower($row['ESTADO']) ?>">
                     <?= $row['ESTADO'];?></th>

                    <!-- Enlaces de acciones: aún sin funcionalidad (falta el href) -->
                    <th><a href="">Editar</a></th>
                    <th><a href="">Eliminar</a></th>
                    <th><a href="">Ver</a></th>
                    
                </tr>
                <?php endwhile; // Fin del ciclo, se repite por cada usuario ?>
            </tbody>

        </table>
    </div>