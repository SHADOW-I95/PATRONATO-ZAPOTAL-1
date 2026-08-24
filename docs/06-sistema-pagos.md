# 06 - Sistema de pagos con comprobante

## Qué es

Como el sistema no tiene integración directa con el banco (BAC), los
usuarios depositan por su cuenta y suben una foto/PDF del comprobante. Un
empleado revisa manualmente en la banca en línea si el depósito llegó, y
aprueba o rechaza desde el sistema.

## Base de datos nueva (`sql_sistema_pagos.sql`)

- **`estado_solicitud_pago`**: catálogo fijo (En revisión / Verificado /
  Rechazado).
- **`solicitudes_pago`**: una fila por cada comprobante subido — quién lo
  subió, para qué vivienda, cuánto declaró pagar, la ruta del archivo, y en
  qué estado está.
- **`solicitud_pago_meses`**: qué meses exactos (año + mes) cubre esa
  solicitud. Está separado de `solicitudes_pago` porque una solicitud puede
  cubrir varios meses a la vez.

## El problema central: no hay forma de saber qué vivienda es "por la
imagen"

El sistema no puede "leer" la foto del comprobante y adivinar de qué
vivienda es. Por eso, la vivienda y el usuario quedan guardados como datos
normales en la base de datos (el usuario ya seleccionó la vivienda en su
perfil antes de subir el archivo) — la imagen es solo evidencia visual para
que el empleado la vea, nunca la fuente de la verdad.

## El código de referencia (para que el empleado encuentre el depósito)

En `SITIO/perfil/perfil.php`, cuando se abre el panel de pago de una
vivienda, se genera un código así:

```php
$codigoReferencia = 'PZ-' . $v['id_vivienda'] . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
// Ejemplo: PZ-34-A1F9
```

Este código se genera **antes** de que el usuario vaya al banco (se
muestra en pantalla junto a los datos bancarios), no después. La idea es
que el usuario lo escriba en el concepto/nota de la transferencia — así el
empleado, al entrar a la banca en línea, puede buscar ese texto exacto en
vez de adivinar por monto y fecha entre varios depósitos parecidos.

## Cálculo de meses pendientes (`ADMIN/modulos/agua/helpers_agua.php`)

Se agregó `obtener_meses_pendientes()`, que devuelve la lista exacta de
meses que debe una vivienda (no solo el conteo, que es lo único que existía
antes):

```php
function obtener_meses_pendientes(PDO $conexion, int $id_vivienda, int $limite = 24): array
{
    // Si ya pagó antes, empieza justo después de su último pago.
    // Si nunca ha pagado, se asume que debe desde el mes actual
    // (no hay forma de saber desde cuándo debería estar pagando,
    // porque la tabla `viviendas` no guarda fecha de registro).
}
```

## El flujo completo

1. **Usuario** (`SITIO/perfil/perfil.php`): toca una vivienda → se
   despliega meses pendientes, monto total, cuenta bancaria (datos de
   ejemplo en `config/pagos.php`) y el código de referencia. Elige cuántos
   meses completos pagar y sube el comprobante.
2. **`SITIO/perfil/subir_pago.php`**: recibe el archivo. Valida que:
   - La vivienda sea realmente del usuario en sesión.
   - No haya ya una solicitud "En revisión" para esa vivienda (evita
     duplicados).
   - El archivo sea imagen o PDF de verdad — se revisa el *contenido* real
     del archivo con `finfo_file()`, no la extensión del nombre (alguien
     podría renombrar un `.exe` a `.jpg`).
   - El archivo pese menos de 8MB.

   El nombre final del archivo lo genera el servidor
   (`comprobante_34_20260822_a1b2c3d4.jpg`), nunca se usa el nombre que
   mandó el navegador — así nadie puede manipular la ruta de guardado.

3. **Empleado** (`ADMIN/modulos/agua/agua.php`): botón "🔔 Notificaciones"
   con contador, lista las solicitudes pendientes con el comprobante y los
   datos. Al hacer clic en "Revisar", se abre un modal con todos los
   detalles.

4. **`ADMIN/modulos/agua/revisar_solicitud.php`**: aquí está la parte más
   importante — **verificar no es un simple sí/no**. El empleado dice
   cuántos de los meses declarados realmente confirma (por si el monto
   real que llegó no coincide con lo que el usuario pidió cubrir):

   ```php
   // Se aplican solo los primeros N meses confirmados por el empleado
   // (los más antiguos primero); el resto queda pendiente para una
   // futura solicitud.
   $mesesAAplicar = array_slice($mesesDeclarados, 0, $meses_confirmados);
   ```

   Al verificar, se reusa exactamente la misma lógica que ya existía en
   `registro_pago.php` (crear un recibo en `pagos_agua` + un detalle por
   mes en `detalle_pago_agua`), y se llama a `refrescar_estado_vivienda()`
   para recalcular si la vivienda queda Pagada, en Mora o Pendiente.

## Seguridad de la carpeta de uploads

`SITIO/uploads/comprobantes/.htaccess` bloquea que cualquier archivo con
extensión de script (`.php`, `.pl`, `.py`, etc.) se pueda ejecutar dentro
de esa carpeta, aunque alguien lograra subir uno disfrazado de imagen. Es
una capa extra de protección, además de la validación de contenido real
del archivo.

## Pendiente (decisión del patronato)

Los datos bancarios en `config/pagos.php` son de ejemplo — hay que
reemplazarlos por los reales del patronato.
