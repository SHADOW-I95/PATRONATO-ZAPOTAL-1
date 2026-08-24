# 03 - Módulo de Configuración: catálogos y "Mi cuenta"

## Qué es

Antes, `configuracion.php` estaba completamente vacío. Ahora tiene 2 (luego
3, ver README 06) pestañas:

- **Catálogos** (solo Administrador): agregar/editar/eliminar Sectores,
  Tipos de servicio y Tipos de reporte — los valores que usan los
  formularios de todo el sistema.
- **Mi cuenta** (cualquier empleado): cambiar su propio teléfono y código
  de acceso.

## Archivos

| Archivo | Qué hace |
|---|---|
| `modulos/configuracion/configuracion.php` | Página principal, arma las pestañas |
| `modulos/configuracion/catalogo_agregar.php` | Inserta un valor nuevo (sector/servicio/tipo de reporte) |
| `modulos/configuracion/catalogo_actualizar.php` | Renombra un valor existente |
| `modulos/configuracion/catalogo_eliminar.php` | Borra un valor (si no está en uso) |
| `modulos/configuracion/cuenta_actualizar.php` | Guarda el teléfono/código del empleado en sesión |
| `assets/js/configuracion.js` | Cambia de pestaña, maneja los formularios por AJAX |

## Cómo funciona el CRUD de catálogos

Los 3 catálogos (sectores, servicios, tipos de reporte) comparten la misma
lógica, pero son tablas distintas en la base de datos. Para no repetir 3
veces el mismo código, `catalogo_agregar.php` y `catalogo_actualizar.php`
usan un mapa de configuración:

```php
$catalogos = [
    'sector'       => ['tabla' => 'sectores',     'columna' => 'nombre_sector'],
    'servicio'     => ['tabla' => 'servicios',    'columna' => 'nombre_servicio'],
    'tipo_reporte' => ['tabla' => 'tipo_reporte', 'columna' => 'tipo_reporte'],
];
```

El formulario manda qué `tipo` es (`sector`, `servicio` o `tipo_reporte`), y
el código busca en ese mapa qué tabla/columna real debe tocar. Esto evita
tener 3 archivos casi idénticos.

**Importante de seguridad**: el nombre de la tabla nunca sale directo del
formulario del usuario — siempre se valida contra este mapa fijo. Si
alguien mandara `tipo=usuarios` tratando de hacerle algo a esa tabla, el
código lo rechaza porque `'usuarios'` no es una llave válida del mapa.

## Por qué "Eliminar" a veces falla a propósito

Sectores, Servicios y Tipos de reporte están relacionados con Viviendas y
Reportes por llave foránea. Si un sector está en uso, MySQL no permite
borrarlo. En vez de mostrar un error feo de base de datos,
`catalogo_eliminar.php` detecta ese error específico (código `23000`) y
redirige con un mensaje claro: *"No se pudo eliminar: ese valor todavía
está siendo usado..."*.

## Cómo funciona "Mi cuenta"

El `id_empleado` que se edita **siempre sale de la sesión**
(`$_SESSION['id']`), nunca del formulario. Esto es intencional: así ningún
empleado puede editar la cuenta de otro cambiando un número en la petición
—  sólo puede tocar la suya propia. El código nuevo también se valida para
que no se repita con el de otro empleado.
