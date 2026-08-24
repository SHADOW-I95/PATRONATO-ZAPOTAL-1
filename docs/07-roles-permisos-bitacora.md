# 07 - Roles y permisos configurables + bitácora (Registro de empleado)

## Qué es

Antes solo existían 2 roles fijos en el código: "Empleado" (ahora
renombrado a **Cobrador**) y "Administrador". Ahora se pueden crear roles
nuevos desde Configuración y decidir, con checkboxes, qué módulos ve cada
uno. También se dejó lista la base para una bitácora de actividad
("Registro de empleado").

## Base de datos (`sql_roles_permisos.sql`)

- **`modulos_sistema`**: catálogo de los módulos que SÍ se pueden asignar a
  un rol (dashboard, usuario, agua, reportes, mapa). A propósito **no**
  incluye `empleados`, `configuracion` ni `registro_empleado` — ver el
  apartado de seguridad más abajo.
- **`permisos_rol`**: qué módulos tiene asignado cada rol. Es una tabla de
  relación simple: si existe una fila `(id_rol=2, id_modulo=3)`, ese rol
  puede ver ese módulo. Si no existe la fila, no puede.
- **`bitacora`**: tabla lista para guardar acciones de empleados (todavía
  no recibe datos — ver la sección "Lo que falta" al final).

## La regla de oro: el Administrador nunca depende de esta tabla

```php
function tienePermiso(string $claveModulo): bool
{
    if (esAdministrador()) {
        return true; // siempre, sin excepción
    }
    // ...solo para otros roles, se consulta permisos_rol...
}
```

Esto es intencional: si el Administrador dependiera de `permisos_rol` como
cualquier otro rol, un error al guardar la matriz de permisos podría dejar
al propio Administrador sin acceso a su propio sistema. Al estar
hardcodeado, eso nunca puede pasar.

## Por qué "Empleados" y "Configuración" NUNCA son asignables

Esto es la parte más importante de todo el diseño, de seguridad:

Si un rol como "Cobrador" pudiera tener acceso al módulo de Empleados,
técnicamente podría **crear un nuevo empleado y asignarle el rol
Administrador** — auto-otorgándose control total del sistema por la puerta
de atrás, sin que el Administrador real se entere. Por eso:

- `empleados.php` sigue con su chequeo original y fijo:
  `if (!esAdministrador())`.
- `configuracion.php` (donde justamente se editan los permisos) también
  queda fijo a Administrador — si un rol pudiera entrar a Configuración,
  podría dares a sí mismo cualquier permiso.
- `registro_empleado.php` (la bitácora) igual, fijo a Administrador — nadie
  debería poder borrar o revisar el rastro de sus propias acciones.

Estos 3 módulos **no aparecen ni como checkbox** en la pantalla de
permisos, y `permisos_guardar.php` los filtra otra vez del lado del
servidor por si alguien intentara mandarlos manipulando la petición:

```php
$modulosValidos = $conexion->query("SELECT clave FROM modulos_sistema")->fetchAll(PDO::FETCH_COLUMN);
$modulos = array_values(array_intersect($modulos, $modulosValidos));
```

Como `empleados`/`configuracion`/`registro_empleado` nunca están en
`modulos_sistema`, `array_intersect` los elimina aunque alguien los
incluyera a la fuerza en el formulario.

## `config/permisos.php` — el corazón del sistema

Tres funciones:

- **`tienePermiso($clave)`**: ¿el empleado en sesión puede ver este módulo?
- **`requerirPermiso($clave)`**: si no puede, lo manda de vuelta al Panel.
  Se llama al principio de cada módulo protegido (una línea por archivo):

  ```php
  require_once __DIR__ . "/../../../config/permisos.php";
  requerirPermiso('agua');
  ```

- **`modulosVisibles()`**: devuelve solo los módulos que el rol actual
  puede ver, para que `barra_lateral.php` arme el menú dinámicamente:

  ```php
  <?php foreach ($modulos as $clave => $datos): ?>
  <a href="<?= $datos['href'] ?>"><?= htmlspecialchars($datos['texto']) ?></a>
  <?php endforeach; ?>
  ```

  Antes, el sidebar tenía cada link escrito a mano en el HTML. Ahora se
  genera solo, según lo que la base de datos diga que ese rol puede ver.

## Pantalla "Roles y permisos" (dentro de Configuración)

- Un formulario simple para agregar un rol nuevo (`rol_agregar.php`).
- Una caja por cada rol (menos Administrador) con un checkbox por módulo.
  Al guardar (`permisos_guardar.php`), se **reemplazan todos** los
  permisos de ese rol: primero se borran sus filas en `permisos_rol`, luego
  se insertan las que quedaron marcadas. Es más simple que comparar cuáles
  se agregaron y cuáles se quitaron.

## Mapa: un caso especial de permiso "parcial"

El Mapa es visible para cualquier rol con el módulo `mapa` asignado, pero
**editar** ubicaciones (agregar/mover/borrar un pin) sigue siendo exclusivo
del Administrador — eso no pasó a ser configurable, porque no es una
decisión de "qué módulos ve cada rol" sino de una acción específica dentro
de un módulo. Esto ya existía desde antes y no se tocó:

```php
$puedeEditar = esAdministrador();
```

## Lo que falta (siguiente ronda)

`config/bitacora.php` ya tiene la función `registrar_actividad()` lista
para usarse:

```php
registrar_actividad('usuario', 'creó', "Registró al usuario {$nombre} (DNI {$dni})");
```

Pero todavía **no se llama desde ningún lado**. Falta agregar esa línea en
cada uno de los ~15 archivos que crean/editan/eliminan algo (usuarios,
pagos, reportes, empleados, catálogos, ubicaciones del mapa) para que la
pantalla de "Registro de empleado" empiece a mostrar actividad real.
