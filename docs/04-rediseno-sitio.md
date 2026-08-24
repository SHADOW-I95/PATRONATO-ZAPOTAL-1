# 04 - Rediseño visual de SITIO (sitio público)

## Qué se hizo

Rediseño completo del sitio público: header, menú hamburguesa, las 4
secciones de la portada, login y perfil. Todo responsivo para celular.

## Bugs reales que se corrigieron de paso

- **El footer nunca se mostraba**: `index.php` nunca hacía `include` de
  `footer.php`. Se agregó la línea que faltaba.
- **Texto invisible en la portada**: el párrafo bajo el título usaba
  `color: transparent` con `-webkit-text-stroke` (contorno), una técnica
  que solo funciona en navegadores basados en Chrome. En Firefox el texto
  simplemente no existía visualmente. Se cambió a un color sólido.
- **Iconos rotos en el perfil**: Font Awesome nunca se cargaba en
  `perfil.php`. Se agregó el `<link>` al CDN.
- **Enlace de "Cerrar sesión" del perfil apuntaba mal**: le faltaba `../`
  en la ruta relativa.
- **Login sin manejo de errores en pantalla**: el formulario hacía un
  envío normal (`<form action="...">`), así que si el nombre/DNI/código no
  coincidían, el navegador te mandaba a `procesar_login.php` y mostraba
  texto plano sin estilo, en una página en blanco.

## Cómo se arregló el error del login (lo más importante técnicamente)

Antes: el `<form>` se enviaba de forma normal, el navegador **navegaba** a
`procesar_login.php`, cuyo `echo` de error se veía como página aparte.

Ahora, en `login.js`:

```javascript
formulario.addEventListener("submit", (e) => {
    e.preventDefault(); // nunca dejamos que el navegador navegue solo
    // ...validaciones del lado del cliente...
    fetch(formulario.action, { method: "POST", body: new FormData(formulario) })
        .then((respuesta) => {
            if (respuesta.redirected) {
                // Login correcto: el servidor mandó una redirección (a
                // perfil.php o al panel ADMIN). fetch la sigue solo, y
                // acá revisamos si eso pasó para navegar manualmente.
                window.location.href = respuesta.url;
            } else {
                // Login incorrecto: el servidor respondió con el
                // mensaje de error como texto plano. Se muestra dentro
                // de la misma tarjeta de login, sin recargar la página.
                return respuesta.text();
            }
        })
        .then((texto) => { if (texto) mostrarError("errorServidor", texto); });
});
```

La clave es `respuesta.redirected`: cuando `procesar_login.php` hace login
correcto, manda un `header('Location: ...')`. El navegador (a través de
`fetch`) sigue esa redirección solo, y `respuesta.redirected` queda en
`true`. Si no hubo redirección, es porque el login falló y lo que llegó es
el mensaje de error de `die(...)`.

## Sistema de variables de color: `SITIO/assets/css/variables.css`

Antes cada archivo CSS tenía su propio azul (`blue` puro en el header,
verde en el login, otro azul distinto en la sección de oficinas). Ahora
todo el sitio usa las mismas variables:

```css
:root {
  --primario: #2563eb;
  --primario-oscuro: #1d4ed8;
  /* ... */
}
```

Y cada archivo usa `var(--primario)` en vez de un color fijo. Esto hace que
cambiar el color de marca en el futuro sea editar un solo archivo, no
buscar en 10.

## Estructura responsiva

- **Header**: `position: sticky` (se queda pegado arriba al hacer scroll).
- **Menú**: en pantallas grandes (`min-width: 900px`) se ve horizontal,
  en línea con el header. En celular, es un panel que entra desde la
  derecha (`transform: translateX(100%)` → `translateX(0)`), controlado por
  el botón hamburguesa.
- **Login**: dos paneles (marca + formulario) en pantallas grandes; en
  celular se oculta el panel de marca y solo se ve el formulario.
- **Perfil**: el sidebar fijo de 250px se convierte en un panel deslizante
  con botón ☰, igual que el menú del sitio público.

## Orden de las secciones

Se reordenó `index.php` para que coincida con el orden del menú: Inicio →
Quiénes somos → Ubicación de oficinas → Reportar queja (antes "Reportar"
aparecía antes que "Ubicación", al revés de como estaba en el menú).
