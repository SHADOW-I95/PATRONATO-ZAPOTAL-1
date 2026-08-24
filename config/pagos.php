<?php
/**
 * Datos de la cuenta bancaria del patronato, usados en la pantalla de pago
 * del usuario (SITIO/perfil) para que sepan a dónde depositar.
 *
 * ESTOS SON DATOS DE EJEMPLO. Cámbialos por los reales del patronato.
 * Más adelante esto se puede mover a la base de datos y editarse desde
 * el módulo de Configuración del panel ADMIN, en vez de tocar código.
 */

define('BANCO_NOMBRE', 'BAC Credomatic');
define('BANCO_NUMERO_CUENTA', '000-000000-0');
define('BANCO_TITULAR', 'Patronato el Zapotal');