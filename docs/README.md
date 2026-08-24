# Documentación del proyecto — Patronato el Zapotal

Un README por cada gran cambio hecho al sistema, en orden cronológico.
Cada uno explica **qué se hizo, por qué, y cómo funciona el código** para
que cualquiera (tú, otro desarrollador, o yo en el futuro) pueda entenderlo
sin tener que releer todo el chat.

| # | Archivo | Tema |
|---|---|---|
| 01 | [`01-correcciones-iniciales.md`](01-correcciones-iniciales.md) | Bugs de arranque: JS/CSS rotos, XSS en Usuarios |
| 02 | [`02-iconos-admin.md`](02-iconos-admin.md) | Sistema de iconos monocromáticos (ADMIN) |
| 03 | [`03-configuracion-catalogos.md`](03-configuracion-catalogos.md) | Módulo Configuración: catálogos y "Mi cuenta" |
| 04 | [`04-rediseno-sitio.md`](04-rediseno-sitio.md) | Rediseño visual completo de SITIO (sitio público) |
| 05 | [`05-responsive-admin.md`](05-responsive-admin.md) | Panel ADMIN responsivo para celular |
| 06 | [`06-sistema-pagos.md`](06-sistema-pagos.md) | Sistema de pagos con comprobante |
| 07 | [`07-roles-permisos-bitacora.md`](07-roles-permisos-bitacora.md) | Roles y permisos configurables + bitácora |
| 08 | [`08-modulo-mapa.md`](08-modulo-mapa.md) | Módulo de Mapa: ubicaciones y permisos de edición |

## Migraciones SQL, en el orden en que hay que correrlas

1. `sql_sistema_pagos.sql`
2. `sql_roles_permisos.sql`

(Las columnas de auditoría del Mapa, descritas en el README 08, ya deberían
estar aplicadas si vienes siguiendo el proyecto desde el principio.)

## Pendientes conocidos

- **`config/pagos.php`**: tiene datos bancarios de ejemplo — falta poner
  los reales del patronato.
- **Bitácora**: la tabla y la función `registrar_actividad()` ya existen
  (ver README 07), pero todavía no se llama desde ningún módulo. Es la
  siguiente tarea grande pendiente.
