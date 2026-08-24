# 05 - Panel ADMIN responsivo (uso en celular)

## Por qué se hizo

El patronato necesita poder usar el sistema desde el celular al registrar
viviendas casa por casa. El panel ADMIN no tenía ninguna adaptación a
pantallas chicas: el sidebar era fijo de 250px, las tablas se desbordaban,
y los modales quedaban apretados.

## Sidebar como panel deslizante (`ADMIN/assets/css/barras.css`)

En escritorio, `.barraLateral` es un elemento normal dentro de un
`display: flex` (ocupa su espacio fijo, empuja el contenido). En celular
(`@media max-width: 900px`), pasa a:

```css
.barraLateral {
  position: fixed;
  transform: translateX(-100%); /* oculto fuera de la pantalla, a la izquierda */
  transition: transform 0.25s ease;
}
.barraLateral.abierto {
  transform: translateX(0); /* visible */
}
```

Al ser `position: fixed`, sale del flujo normal del `display:flex`, así que
el contenido principal automáticamente ocupa el 100% del ancho disponible
sin que haya que tocar nada más.

## `ADMIN/assets/js/layout.js`

Dos funciones simples que agregan/quitan la clase `.abierto`:

```javascript
function abrirSidebarAdmin() {
    document.getElementById('barraLateralAdmin')?.classList.add('abierto');
    document.getElementById('overlaySidebarAdmin')?.classList.add('visible');
}
```

El botón hamburguesa (agregado en `barra_superior.php`) llama a
`abrirSidebarAdmin()`. Al tocar un link del menú o el fondo oscuro
(`overlay`), se cierra solo — así no se queda tapando la pantalla después
de navegar a otro módulo.

## Tablas: scroll horizontal en vez de romperse

En vez de reescribir cada tabla para que se vea distinto en celular (mucho
trabajo, un módulo a la vez), se aplicó una solución centralizada en
`global.css`:

```css
@media (max-width: 900px) {
  .seccion {
    overflow-x: auto; /* el contenedor se puede deslizar de lado a lado */
  }
  .tabla_datos {
    min-width: 560px; /* la tabla no se aplasta, se desliza en su lugar */
  }
}
```

Como **todas** las tablas del sistema están dentro de un `<div
class="seccion">`, esta única regla arregla usuario2.php, empleados.php,
agua.php y reportes.php al mismo tiempo, sin tocar ninguno de esos
archivos.

## Formularios y modales

```css
@media (max-width: 900px) {
  .informacion, .vivienda {
    grid-template-columns: 1fr; /* de 2 columnas a 1 */
  }
  .modal-contenido {
    width: 100%;
    height: 100%; /* modal a pantalla completa en celular */
    border-radius: 0;
  }
}
```

## El módulo de Mapa no se tocó

Quien construyó `mapa.php` ya le había puesto su propio responsive dentro
de su `<style>` interno (el panel lateral se apila arriba del mapa en
pantallas chicas). Se revisó y ya estaba bien resuelto.
