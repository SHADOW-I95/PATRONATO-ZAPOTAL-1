# 02 - Sistema de iconos monocromáticos (ADMIN)

## Qué es

Un set de iconos en blanco/negro (sin colores) para todo el panel ADMIN:
menú lateral, botón de cerrar sesión, botones de Editar/Eliminar/Ver en las
tablas, botón "+ Nuevo", y los accesos rápidos del dashboard.

## Archivo principal: `ADMIN/assets/css/iconos.css`

Cada icono es un SVG simple, hecho a mano (no es una librería externa como
Font Awesome), guardado como variable CSS en formato "data URI":

```css
:root {
  --icono-editar: url("data:image/svg+xml,%3Csvg...%3E");
}
```

## Cómo funciona la técnica de `mask-image`

En vez de poner el SVG como imagen de fondo (`background-image`), se usa
como **máscara** (`mask-image` / `-webkit-mask-image`):

```css
.btn-editar::before {
  background-color: currentColor; /* el color lo decide el CSS normal */
  mask-image: var(--icono-editar);
  mask-size: contain;
}
```

La diferencia importante: con `background-image`, el color del icono queda
fijo (el que tenga el SVG). Con `mask-image`, el SVG solo define la
*forma* (qué partes son visibles), y el color real lo pone
`background-color: currentColor` — que toma el color de texto que ya tenga
ese elemento en ese momento. Así, el mismo icono se ve blanco en el sidebar
oscuro y negro/gris en los botones sobre fondo blanco, sin tener que crear
dos versiones del archivo.

## Dónde se aplican (todo por CSS, sin tocar cada PHP)

- **Sidebar**: `.barraNavegacion a[href*="modulo=dashboard"]::before` — se
  detecta qué icono poner según el texto del link (`href`), no hay que
  agregar clases a mano en `barra_lateral.php`.
- **Botones de tabla**: cualquier botón con clase `.btn-editar`,
  `.btn-eliminar` o `.btn-ver` en cualquier módulo recibe su icono
  automáticamente.
- **Dashboard**: usa un `<span class="acceso-icono">` que ya existía vacío
  en el HTML original — solo se le agregó el `mask-image` correspondiente.

## Por qué se hizo así

Permite agregar/cambiar iconos en un solo archivo (`iconos.css`) sin tener
que editar los ~10 módulos que usan botones de Editar/Eliminar/Ver. Si más
adelante se agrega un módulo nuevo con esos mismos botones, el icono ya
aparece solo, sin trabajo extra.
