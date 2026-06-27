<?php
  include('conexion.php');
  
  $con = connection();

  $sql = "SELECT * FROM usuarios";
  $query = mysqli_query($con, $sql);

?>

 <div class="contenido">

        <div class="encabezado">
            <h1>Usuarios</h1>
        </div>

        <div class="buscador">
            <input type="text" placeholder="Buscar usuario...">

            <button class="btn-nuevo" id="abrir-modal">
                + Nuevo Usuario
            </button>

        </div>
    </div>

    <div class="modal" id="modal">
        <div class="modal-contenido">
            <span class="cerrar" id="cerrar-modal">&times;</span>
            <h2>Nuevo usuario</h2>

            <form action="agregar.php" method="POST">

                <div class="infromacion_personal">
                    <span>infromacion personal</span>

                    <label for="">DNI</label>
                    <input type="number" placeholder="Numero identidad" name="dni" >

                    <label for="">Nombre</label>
                    <input type="text" placeholder="Nombre" name="nombre">

                    <label for="">Apellido</label>
                    <input type="text" placeholder="Apellido" name="apellido">

                    <label for="">Fecha de Nacimiento</label>
                    <input type="datetime-local" placeholder="dd/mm/aa" name="fecha_nac">

                    <label for="">Telefono</label>
                    <input type="number" placeholder="Telefono" name="telefono">

                    <label for="">Correo</label>
                    <input type="email" placeholder="Email" name="email">
                </div>

                <div class="inormacion_servicios">
                    <span>Servivios</span>

                    <label for="">Sector</label>
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
                    <input type="number" placeholder="Numero casa" name="nuumero_casa">

                    <label for="">Tipo Servicio</label>
                    <select id="" name="tipo_servicio">
                        <option>Casa</option>
                        <option>Apartamento</option>
                        <option>Negocio</optio>
                        <option>Alquilado</option>
                    </select>

                    <label for="">Cantidad Propiedades</label>
                    <input type="number" placeholder="Cntidad de propiedades" name="cant_propiedades">

                    <label for="">Cuota Mensual</label>
                    <input type="number" placeholder="Cuota Mensual" name="cuota">

                    <label for="">Estado</label>
                    <select id="" name="estado">
                        <option>Activo</option>
                        <option>Inactivo</option>
                    </select>

                    <label for="">Obervaciones</label>
                    <input type="text" placeholder="Observaciones" name="observaciones">

                </div>
            </form>

        </div>
    </div>


    <div class="div-table">
        <table class="table">

            <thead class="thead">
                <tr>
                    <th>#</th>
                    <th>DNI</th>
                    <th>NOMBRE</th>
                    <th>APELLIDO</th>
                    <th>FECHA_NACIMIENTO</th>
                    <th>TELEFONO</th>
                    <th>CORREO</th>
                    <th>SECTOR</th>
                    <th>NUMERO_CASA</th>
                    <th>TIPO_SERVICIO</th>
                    <th>CANT_PROPIEDADES</th>
                    <th>CUOTA_MEN</th>
                    <th>ESTADO</th>
                    <th>FECHA_REGISTRO</th>
                    <th>OBSERVACIONES</th>

                    <th>EDITAR</th>
                    <th>ELIMINAR</th>
                    <th>VER</th>
                </tr>
            </thead>

            <tbody class="tbody">
              <?php while($row = mysqli_fetch_array($query)): ?>
                <tr>
                    <th><?= $row['id_usuario'] ?></th>
                    <th><?= $row['DNI'] ?></th>
                    <th><?= $row['NOMBRE'] ?></th>
                    <th><?= $row['APELLIDO'] ?></th>
                    <th><?= $row['FECHA_NACIMIENTO'] ?></th>
                    <th><?= $row['TELEFONO'] ?></th>
                    <th><?= $row['CORREO'] ?></th>
                    <th><?= $row['SECTOR'] ?></th>
                    <th><?= $row['NUMERO_CASA'] ?></th>
                    <th><?= $row['TIPO_SERVICIO'] ?></th>
                    <th><?= $row['CANT_PROPIEDADES'] ?></th>
                    <th><?= $row['CUOTA_MENSUAL'] ?></th>
                    <th><?= $row['ESTADO'] ?></th>
                    <th><?= $row['FECHA_REGISTRO'] ?></th>
                    <th><?= $row['OBSERVACIONES'] ?></th>

                    <th><a href="">Editar</a></th>
                    <th><a href="">Eliminar</a></th>
                    <th><a href="">Ver</a></th>
                    
                </tr>
                <?php endwhile; ?>
            </tbody>

        </table>
    </div>
