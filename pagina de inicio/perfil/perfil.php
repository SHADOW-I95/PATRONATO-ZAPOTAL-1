<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Perfil - Patronato de Agua</title> <!-- titulo de la pagina -->

<link rel="stylesheet" href="style.css"> <!-- Enlace al archivo CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> <!-- Enlace a Font Awesome para iconos -->
</head>
<body>

<div class="sidebar"> <!-- Contenedor de la barra lateral -->
  <div class="sidebar-logo"> <!-- Contenedor del logo -->
    <i class="fa-solid fa-droplet"></i> <!-- Icono de gota de agua -->
    <span>PATRONATO<br>DE AGUA</span> <!-- texto que aparece al lado de la gota -->
  </div>

  <p class="sidebar-titulo">MENU PRINCIPAL</p> <!-- Título del menú principal -->

  <a href="../pagina de inicio/Pagina_inicio/index.php"><i class="fa-solid fa-house"></i> Inicio</a> <!-- Enlace a la página de inicio con icono de casa -->
  <a href="perfil.php" class="activo"><i class="fa-solid fa-user"></i> Mi Perfil</a> <!-- Enlace a la página de perfil con icono de usuario y clase "activo" para resaltar la página actual -->
  <a href="mis_pagos.php"><i class="fa-solid fa-file-invoice-dollar"></i> Mis Pagos</a> <!-- Enlace a la página de mis pagos con icono de factura -->
  <a href="facturas.php"><i class="fa-solid fa-file-lines"></i> Facturas</a> <!-- Enlace a la página de facturas con icono de documento -->
  <a href="mi_consumo.php"><i class="fa-solid fa-tint"></i> Mi Consumo</a> <!-- Enlace a la página de mi consumo con icono de gota -->
  <a href="avisos.php"><i class="fa-solid fa-bell"></i> Avisos</a> <!-- Enlace a la página de avisos con icono de campana -->
  <a href="reportes.php"><i class="fa-solid fa-chart-bar"></i> Reportes</a> <!-- Enlace a la página de reportes con icono de gráfico -->
  <a href="contactos.php"><i class="fa-solid fa-address-book"></i> Contacto</a> <!-- Enlace a la página de contactos con icono de libreta -->
</div>

<div class="contenido"> <!-- Contenedor del contenido principal -->

  <div class="topbar"> <!-- Contenedor de la barra superior -->
    <div class="topbar-derecha"> <!-- Contenedor de los elementos de la derecha -->
      <div class="topbar-campana"> <!-- Contenedor de la campana -->
      </div> 
        <div class="topbar-usuario"> <!-- Contenedor del usuario -->
       
        <div class="topbar-usuario-texto"> <!-- Contenedor del texto del usuario -->
          <span class="nombre"><?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></span> <!-- Nombre del usuario -->
          <span class="abonado">Abonado #<?php echo htmlspecialchars($_SESSION['recibo'] ?? ''); ?></span> <!-- Número de recibo del usuario -->
        </div>
      </div>
    </div>
  </div>

  <div class="perfil-header"> <!-- Contenedor del encabezado del perfil -->
    <div class="perfil-header-icon"> <!-- Contenedor del icono del encabezado -->
      <i class="fa-solid fa-user"></i> <!-- Icono del encabezado -->
    </div>
    <div>
      <h1>Mi Perfil</h1> <!-- Título del encabezado -->
      <p>Consulta y administra tu información personal y de servicio.</p> <!-- Descripción del encabezado -->
    </div>
  </div>

  <div class="perfil-card"> <!-- Contenedor de la tarjeta del perfil -->

    <div class="perfil-foto-col"> <!-- Contenedor de la foto del perfil -->
      <div class="perfil-foto"> <!-- Contenedor de la foto -->
        <?php if (!empty($_SESSION['foto'])): ?> <!-- Si hay una foto de perfil -->
          <img src="<?php echo htmlspecialchars($_SESSION['foto']); ?>" alt="Foto de perfil"> <!-- Muestra la foto de perfil -->
        <?php else: ?> <!-- Si no hay una foto de perfil -->
          <i class="fa-solid fa-user perfil-foto-placeholder"></i> <!-- Icono del placeholder de la foto -->
        <?php endif; ?> <!-- Si no hay una foto de perfil -->
      </div>
      <button class="btn-cambiar-foto" type="button"> 
        <i class="fa-solid fa-camera"></i> Cambiar foto</button> <!-- Botón para cambiar la foto -->
    </div>

    <div class="perfil-info-col">
      <h2><i class="fa-solid fa-address-card"></i> Información Personal</h2> <!-- Título de la sección de información personal -->

      <div class="perfil-dato">
        <span class="perfil-label">Nombre completo:</span> <!-- Etiqueta para el nombre completo -->
        <span class="perfil-valor"><?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></span> <!--  Muestra el nombre completo del usuario -->
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">Número de Recibo:</span> <!-- Etiqueta para el número de recibo -->
        <span class="perfil-valor perfil-link"><?php echo htmlspecialchars($_SESSION['recibo'] ?? ''); ?></span> <!-- Muestra el número de recibo del usuario -->
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">Identidad:</span> <!-- Etiqueta para la identidad -->
        <span class="perfil-valor"><?php echo htmlspecialchars($_SESSION['identidad'] ?? ''); ?></span> <!-- Muestra la identidad del usuario -->
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">Teléfono:</span> <!-- Etiqueta para el teléfono -->
        <span class="perfil-valor"> 
          <?php echo htmlspecialchars($_SESSION['telefono'] ?? ''); ?> <!-- Muestra el teléfono del usuario -->
          <?php if (!empty($_SESSION['telefono'])): ?> <!-- Si hay un teléfono registrado -->
            <i class="fa-brands fa-whatsapp perfil-whatsapp"></i> <!-- Icono de WhatsApp al lado del teléfono -->
          <?php endif; ?> <!-- Fin de la condición -->
        </span>
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">Correo electrónico:</span> <!-- Etiqueta para el correo electrónico -->
        <span class="perfil-valor"><?php echo htmlspecialchars($_SESSION['correo'] ?? ''); ?></span> <!-- Muestra el correo electrónico del usuario -->
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">No. Casa:</span> <!-- Etiqueta para el número de casa -->
        <span class="perfil-valor"><?php echo htmlspecialchars($_SESSION['no_casa'] ?? ''); ?></span>
      </div>

      <div class="perfil-dato">
        <span class="perfil-label">Barrio / Colonia:</span> <!-- Etiqueta para el barrio o colonia -->
        <span class="perfil-valor"><?php echo htmlspecialchars($_SESSION['colonia'] ?? ''); ?></span>
      </div>

    </div>
  </div>

  <div class="perfil-acciones"> <!-- Contenedor para las acciones del perfil -->
    <button class="btn-editar" type="button"> <!-- Botón para editar la información del perfil -->
      <i class="fa-solid fa-pen"></i> Editar información
    </button>
  </div>

  <div class="dashboard-grid"> <!-- Contenedor para el grid del dashboard -->

    <div class="resumen-pagos"> <!-- Contenedor para el resumen de pagos -->
      <h3><i class="fa-solid fa-dollar-sign resumen-icono"></i> Resumen de Pagos</h3>

      <div class="resumen-grid"> <!-- Contenedor para el grid del resumen -->
        <div class="resumen-item resumen-verde">
          <i class="fa-solid fa-file-invoice"></i> <!-- Icono para el saldo pendiente -->
          <span class="resumen-label">Saldo pendiente</span>
          <span class="resumen-valor">L <?php echo htmlspecialchars($_SESSION['saldo_pendiente'] ?? '0.00'); ?></span> <!-- Valor del saldo pendiente -->
        </div>
        <div class="resumen-item resumen-naranja">
          <i class="fa-solid fa-calendar"></i> <!-- Icono para el próximo pago -->
          <span class="resumen-label">Próximo pago</span>
          <span class="resumen-valor"><?php echo htmlspecialchars($_SESSION['proximo_pago'] ?? ''); ?></span> <!-- Valor del próximo pago -->
        </div>
        <div class="resumen-item resumen-azul">
          <i class="fa-solid fa-receipt"></i>
          <span class="resumen-label">Último pago</span>
          <span class="resumen-valor">L <?php echo htmlspecialchars($_SESSION['ultimo_pago'] ?? '0.00'); ?></span> <!-- Valor del último pago -->
        </div>
        <div class="resumen-item resumen-morado">
          <i class="fa-solid fa-droplet"></i>
          <span class="resumen-label">Consumo del mes</span>
          <span class="resumen-valor"><?php echo htmlspecialchars($_SESSION['consumo_mes'] ?? '0'); ?> m³</span> <!-- Valor del consumo del mes -->
        </div>
      </div>

      <button class="btn-historial" type="button"> <!-- Botón para ver el historial de pagos -->
        <i class="fa-solid fa-clock-rotate-left"></i> Ver historial de pagos
      </button>
    </div>

    <div class="avisos-importantes"> <!-- Contenedor para los avisos importantes -->
      <h3><i class="fa-solid fa-triangle-exclamation avisos-icono"></i> Avisos Importantes</h3>
      <a href="avisos.php" class="ver-todos">Ver todos</a>

      <?php
        // ReemplazaR esto con lo de esa cosa de la base de datos   
        $avisos = $avisos ?? [];
      ?>

      <?php if (empty($avisos)): ?> <!-- Si no hay avisos -->
        <p class="avisos-vacio">No hay avisos por el momento.</p>
      <?php else: ?>
        <?php foreach ($avisos as $aviso): ?> <!-- Iterar sobre cada aviso -->
          <div class="aviso-item">
            <i class="fa-solid fa-droplet aviso-item-icono"></i> <!-- Icono para el aviso -->
            <div>
              <p class="aviso-titulo"><?php echo htmlspecialchars($aviso['titulo']); ?></p> <!-- Título del aviso -->
              <p class="aviso-texto"><?php echo htmlspecialchars($aviso['texto']); ?></p> <!-- Texto del aviso -->
              <p class="aviso-fecha"><?php echo htmlspecialchars($aviso['fecha']); ?></p> <!-- Fecha del aviso -->
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>

</div><!-- cierre de .contenido -->

</body>
</html>