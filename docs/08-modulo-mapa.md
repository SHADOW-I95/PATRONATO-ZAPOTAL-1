# 08 - Módulo de Mapa

## Qué es

Un mapa (Leaflet + OpenStreetMap, sin costo ni llave de API) donde se
guarda la ubicación GPS de cada vivienda. Cualquier rol con permiso al
módulo puede **verlo**; solo el Administrador puede **editarlo**
(agregar, mover o quitar un punto).

## Columnas que necesita la tabla `viviendas`

```sql
latitud, longitud                        -- coordenadas del punto
id_empleado_registro_ubicacion           -- quién la registró
fecha_registro_ubicacion
id_empleado_modifico_ubicacion           -- quién la modificó por última vez
fecha_modificacion_ubicacion
```

Estas dos últimas parejas (registro/modificación) son un mini historial de
auditoría — sin necesitar todavía la bitácora general del README 07, ya se
puede saber quién tocó la ubicación de una vivienda y cuándo.

## El bug original (por si vuelve a aparecer)

Cuando se recibió por primera vez este módulo, el código ya intentaba leer
y escribir esas columnas de auditoría, pero **no existían todavía en la
base de datos** — eso producía un error de "columna desconocida" al
guardar. Se resolvió agregando las columnas con `ALTER TABLE`. Si alguna
vez se restaura un respaldo viejo de la base de datos sin estas columnas,
el síntoma va a ser el mismo error.

## Doble validación de permisos (nunca confiar solo en ocultar botones)

```php
// mapa.php
requerirPermiso('mapa');        // ¿puede ver el módulo?
$puedeEditar = esAdministrador(); // ¿puede editar puntos?
```

`$puedeEditar` se usa para **ocultar** los botones de edición en el HTML si
es `false`. Pero eso es solo cosmético — un Cobrador con conocimientos
técnicos podría llamar directo a `guardar_ubicacion.php` sin pasar por la
interfaz. Por eso, tanto `guardar_ubicacion.php` como
`eliminar_ubicacion.php` **repiten la validación** de forma independiente:

```php
if (!esAdministrador()) {
    http_response_code(403);
    exit;
}
```

La regla general en todo el proyecto es esta: cualquier permiso que decide
qué botón se muestra en pantalla, se vuelve a comprobar en el archivo que
realmente hace el cambio en la base de datos.
