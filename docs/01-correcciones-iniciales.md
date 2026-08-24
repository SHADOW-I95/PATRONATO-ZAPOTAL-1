# 01 - Correcciones iniciales del panel ADMIN

## Qué se arregló

El panel tenía varios recursos rotos que no se notaban a simple vista pero
rompían funcionalidad real:

1. **`empleado.js` vs `empleados.js`**: `ADMIN/index.php` cargaba un archivo
   que no existía (le faltaba la "s"). Como resultado, todo el JavaScript del
   módulo de Empleados (abrir modales, editar, ver) nunca se ejecutaba.
   Se corrigió el nombre en el `<script src="...">`.

2. **`factura.css` no existía**: se quitó esa línea de `<link>`. El archivo
   `factura.php` ya trae sus propios estilos dentro de una etiqueta
   `<style>`, así que no se perdió nada.

3. **XSS en el módulo de Usuarios**: `usuario2.php` imprimía los datos del
   usuario (nombre, DNI, teléfono) directo en la tabla, sin pasar por
   `htmlspecialchars()`. Si alguien registraba un usuario con código HTML/JS
   en el nombre, ese código se ejecutaría en el navegador de quien viera la
   tabla después. Se agregó `htmlspecialchars()` a cada celda, igual que ya
   se hacía en el módulo de Empleados.

## Cómo funciona `htmlspecialchars()`

Convierte caracteres como `<`, `>`, `"` en su versión de texto plano
(`&lt;`, `&gt;`, `&quot;`), para que el navegador los muestre como texto en
vez de interpretarlos como código HTML. Se usa en **todo output** que venga
de datos guardados por un usuario (nombre, DNI, descripción de reporte,
etc.) — nunca hay que confiar en que lo que hay en la base de datos sea
"seguro" para imprimir directo.

## Por qué importa

Estas correcciones no agregan funciones nuevas, pero sin ellas el módulo de
Empleados estaba prácticamente roto (los botones no hacían nada) y había un
hueco de seguridad real en Usuarios.
