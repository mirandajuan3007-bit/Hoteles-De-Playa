# Casos de uso - Sistema de reservaciones Hoteles de Playa

Cada caso de uso es una feature. Los casos de uso estan agrupados en 6 modulos.

## Actores

- **Administrador:** acceso total; es el unico que gestiona el catalogo de habitaciones.
- **Huesped registrado:** acceso a todo, excepto a la gestion del catalogo.
- **Visitante (no registrado):** solo puede ver habitaciones y buscar; no puede reservar.

## Modulos

| Modulo | Nombre | Casos de uso |
|--------|--------|--------------|
| M1 | Autenticacion | CU-01, CU-02, CU-03, CU-04 |
| M2 | Administracion de habitaciones | CU-05, CU-06, CU-07 |
| M3 | Catalogo de habitaciones | CU-08, CU-09 |
| M4 | Busqueda y reportes | CU-10 |
| M5 | Carrito (cookie) | CU-11, CU-12, CU-13, CU-14 |
| M6 | Pago y reservaciones | CU-15 |

---

## M1 - Autenticacion

### CU-01 Registrar huesped
**Que es:** un visitante crea su cuenta para poder reservar.
**Como se veria:** opcion "Registrarse" en el menu. Formulario con nombre, correo, usuario y contraseña. Al guardar, muestra un mensaje de exito y lleva al inicio de sesion.

### CU-02 Iniciar sesion
**Que es:** el usuario entra al sistema con su usuario y contraseña; el sistema detecta si es administrador o huesped.
**Como se veria:** opcion "Iniciar sesion" en el menu. Formulario de usuario y contraseña. Si los datos son incorrectos, muestra un mensaje de error en el mismo formulario. Si son correctos, el menu cambia segun el rol.

### CU-03 Cerrar sesion
**Que es:** el usuario termina su sesion.
**Como se veria:** opcion "Cerrar sesion" en el menu del usuario. Pide confirmacion y regresa a la pagina de inicio como visitante.

### CU-04 Restringir acceso a paginas protegidas
**Que es:** el sistema bloquea paginas segun el rol (por ejemplo, el huesped no entra a la administracion de habitaciones y el visitante no entra al carrito ni al pago).
**Como se veria:** si alguien abre una pagina sin permiso (aunque escriba la URL directamente), lo redirige al inicio de sesion o al inicio con el mensaje "Acceso no autorizado". Las opciones sin permiso no aparecen en el menu.

---

## M2 - Administracion de habitaciones (solo administrador)

### CU-05 Agregar habitacion
**Que es:** el administrador da de alta una habitacion nueva con una o mas imagenes.
**Como se veria:** menu "Administracion > Agregar habitacion". Formulario con los datos de la habitacion (nombre, categoria, descripcion, precio, cantidad disponible) y un campo para subir una o varias imagenes. Botones "Guardar" y "Cancelar".

### CU-06 Editar habitacion
**Que es:** el administrador modifica cualquier dato de una habitacion, incluidas sus imagenes, excepto el identificador.
**Como se veria:** en el listado de administracion, boton "Editar" en cada habitacion. Abre el formulario con los datos cargados; el identificador se muestra pero no se puede modificar. Permite quitar las imagenes actuales y subir nuevas.

### CU-07 Eliminar habitacion
**Que es:** el administrador borra una habitacion del catalogo.
**Como se veria:** boton "Eliminar" en cada habitacion del listado de administracion. Pide confirmacion antes de borrar y luego muestra un mensaje de exito.

---

## M3 - Catalogo de habitaciones (todos los usuarios)

### CU-08 Listar habitaciones por categoria
**Que es:** muestra las habitaciones registradas agrupadas por categoria, generadas desde la base de datos.
**Como se veria:** menu "Habitaciones" con un submenu por categoria. Cada categoria muestra tarjetas con imagen, nombre, precio y disponibilidad. El huesped ve el boton "Agregar al carrito"; el visitante no.

### CU-09 Ver resumen de habitacion en ventana emergente
**Que es:** consultar informacion sintetizada de un tipo de habitacion sin salir de la pagina.
**Como se veria:** boton "Ver detalles" en cada tarjeta. Abre una ventana emergente con imagen, descripcion corta, precio y disponibilidad, y un boton para cerrarla.

---

## M4 - Busqueda y reportes (todos los usuarios)

### CU-10 Buscar habitaciones
**Que es:** buscar habitaciones por criterios y mostrar los resultados en formato de reporte.
**Como se veria:** barra o pagina "Buscar" en el menu, con campos de busqueda (por ejemplo: texto, categoria, rango de precio). Los resultados aparecen en una tabla tipo reporte con los datos de cada habitacion encontrada.

---

## M5 - Carrito (cookie) (huesped, con cookies y JavaScript)

### CU-11 Agregar habitacion al carrito
**Que es:** el huesped agrega un tipo de habitacion a su carrito; se guarda en cookies, no en la sesion.
**Como se veria:** boton "Agregar al carrito" en la tarjeta de la habitacion. El icono del carrito en el menu actualiza el numero de habitaciones seleccionadas.

### CU-12 Editar cantidad en el carrito
**Que es:** el huesped cambia cuantas habitaciones de un mismo tipo quiere reservar.
**Como se veria:** en la pagina "Mi carrito", cada fila tiene un campo de cantidad (o botones + / -). Al cambiarla, los costos se recalculan al instante.

### CU-13 Eliminar habitacion del carrito
**Que es:** el huesped quita un tipo de habitacion del carrito.
**Como se veria:** boton "Quitar" en cada fila del carrito. Pide confirmacion y actualiza la tabla y los totales.

### CU-14 Ver subtotales y total
**Que es:** mostrar el costo por tipo de habitacion y el costo total de la reservacion.
**Como se veria:** tabla en "Mi carrito" con las columnas: habitacion, precio unitario, cantidad y subtotal. Al final, el total de la reservacion y el boton "Pagar".

---

## M6 - Pago y reservaciones (huesped)

### CU-15 Pagar reservacion
**Que es:** el huesped confirma y paga la reservacion; despues del pago se descuenta en la base de datos la disponibilidad de las habitaciones reservadas.
**Como se veria:** desde "Pagar", se abre un formulario de pago con el resumen de la reservacion. Pide confirmacion, procesa el pago, muestra un mensaje de reservacion exitosa, vacia el carrito y la disponibilidad mostrada en el catalogo baja.

---

## Reglas generales (aplican a todo el sistema)

- Validar con JavaScript todos los campos de todos los formularios.
- Toda accion importante pide confirmar o cancelar antes de continuar.
- Todos los procesos tienen un boton "Cancelar".
- Menu principal con menus desplegables, visible en todas las paginas (sin callejones sin salida).
- Todas las paginas salen de una misma plantilla de diseño.
- Funciona igual en cualquier navegador.
- Imagenes nitidas y colores apropiados.
- Todo el texto de la aplicacion en español, sin errores de ortografia.

## Reglas del repositorio

- No se debe hacer push directo a `main` ni a `develop`.
- El detalle de como trabajar con Git esta en la seccion [Como trabajar en Git](#como-trabajar-en-git).

---

## Reparto de los modulos

El equipo es de **4 integrantes** y hay **15 casos de uso**, asi que no se pueden repartir exactamente iguales: tres integrantes tienen 4 CU y uno tiene 3. Ademas, cada quien tiene una tarea de la base comun.

| Integrante | Modulos | Casos de uso | Total | Tarea de la base comun | Resumen de lo que tiene que hacer |
|------------|---------|--------------|-------|------------------------|-----------------------------------|
| Juan | M1 Autenticacion | CU-01, CU-02, CU-03, CU-04 | 4 | Plantilla de diseño y menu principal | Registro, inicio y cierre de sesion y control de acceso por rol (que paginas y opciones del menu ve cada actor). |
| isasias | M2 Administracion de habitaciones | CU-05, CU-06, CU-07 | 3 | Script `.sql` de la BD (habitaciones, categorias, imagenes, administrador y datos de prueba) | Alta, edicion y baja de habitaciones con subida de imagenes (solo administrador). |
| Jarib | M3 Catalogo + M4 Busqueda y reportes + M6 Pago y reservaciones | CU-08, CU-09, CU-10, CU-15 | 4 | Tabla de reservaciones en el script `.sql` (coordinado con isais) | Catalogo por categoria con tarjetas y ventana emergente, busqueda con resultados en tabla tipo reporte y el pago, que descuenta la disponibilidad en la BD y vacia el carrito. |
| jorge | M5 Carrito (cookie) | CU-11, CU-12, CU-13, CU-14 | 4 | Funciones comunes de validacion con JavaScript | Carrito guardado en cookie: agregar, editar cantidad, quitar, subtotales y total. |


### Base comun (se hace primero, antes de los modulos)

Estas piezas las usan todos, asi que se terminan y se suben a `develop` antes de empezar los CU:

1. **Plantilla de diseño y menu** (Juan): encabezado, menu desplegable, pie de pagina y hoja de estilos general. El menu ya debe tener el espacio para las opciones de todos los modulos.
2. **Base de datos** (isais): script `.sql` con todas las tablas, el usuario administrador y datos de prueba (categorias y algunas habitaciones con imagen).
3. **Tabla de reservaciones** ( jarib ): se agrega al mismo script `.sql`, en coordinacion con isais.
4. **Validaciones comunes** (jorge): archivo de funciones JavaScript reutilizables (campo vacio, correo, numero, confirmar/cancelar) que usan todos los formularios.

### Acuerdos entre modulos (contratos)

Para que al final todo se una sin problemas, estos nombres se respetan **en todo el proyecto**. Si alguien necesita cambiar uno, lo avisa al equipo antes de hacerlo.

| Que | Acuerdo (propuesta, confirmar en equipo) | Lo crea | Lo usan |
|-----|------------------------------------------|---------|---------|
| Datos del usuario en la sesion | `usuario_id`, `usuario_nombre`, `rol` (`admin` o `huesped`) | M1 | M2, M3, M5, M6 |
| Visitante | No existe `rol` en la sesion | M1 | Todos |
| Cookie del carrito | Nombre `carrito`; contenido en JSON: `[{"id": 3, "cantidad": 2}]` | M5 | M3 (boton "Agregar al carrito"), M6 |
| Tabla de habitaciones | Columnas definidas en el script `.sql` (id, nombre, categoria, descripcion, precio, disponibles) | M2 | M3, M4, M5, M6 |
| Imagenes | Una sola carpeta para las imagenes subidas; en la BD solo se guarda la ruta | M2 | M3, M4 |
| Paginas | Cada modulo trabaja en su propia carpeta (por ejemplo `autenticacion/`, `admin/`, `catalogo/`, `busqueda/`, `carrito/`, `pago/`) | Cada dueño | — |

---

## Reglas para trabajar en equipo

1. **Cada quien trabaja en su parte.** Solo se modifican los archivos de tu modulo. Si necesitas cambiar algo de otro modulo, se lo pides al dueño o lo hablan antes.
2. **Los archivos compartidos se cuidan.** La plantilla, el menu, la hoja de estilos, el script `.sql` y las validaciones comunes solo se cambian por Pull Request y avisando al equipo.
3. **Se respetan los contratos.** Los nombres de variables de sesion, de la cookie, de tablas y de columnas son los de la tabla de acuerdos.
4. **Todo usa la misma plantilla.** Ninguna pagina se hace "suelta": todas incluyen el mismo encabezado, menu y pie.
5. **Cada CU cumple las reglas generales.** Validacion con JavaScript, confirmar/cancelar, boton "Cancelar" y textos en español sin faltas de ortografia.
6. **No esperes a los demas para avanzar.** Si dependes de un modulo que no esta listo, simulalo: por ejemplo, pon a mano `rol = huesped` en la sesion para probar el carrito, o crea una cookie de prueba para probar el pago.
7. **Un CU esta terminado cuando:** funciona, cumple lo que dice "Como se veria", valida sus formularios, se probo en al menos dos navegadores y cuando ya creeas que esta tu mismo haces el merge
8. **Comunicacion.** Avisar en el grupo cuando se sube algo a `develop`, cuando se cambia un archivo compartido o cuando alguien se atora.
9. **Las preguntas abiertas se deciden en equipo.** Si una decision afecta a otro modulo (por ejemplo, si la reservacion lleva fechas), no se decide solo.

---

## Como trabajar en Git

### Ramas

| Rama | Para que sirve | Quien escribe |
|------|----------------|---------------|
| `main` | Version estable para entregar. | Solo por Pull Request desde `develop`, con todo el equipo de acuerdo. |
| `develop` | Integracion: aqui se junta el trabajo de todos. | Solo por Pull Request desde las ramas de feature o fix. |
| `feature/cu-XX-nombre-corto` | Un caso de uso (por ejemplo `feature/cu-01-registrar-huesped`). | El dueño del CU. |
| `fix/descripcion-corta` | Corregir un error encontrado en `develop`. | Quien lo encuentre o el dueño del modulo. |

### Flujo para cada caso de uso

```bash
# 1. Partir siempre de develop actualizado
git checkout develop
git pull origin develop

# 2. Crear la rama del CU
git checkout -b feature/cu-01-registrar-huesped

# 3. Trabajar y hacer commits pequeños
git add <archivos>
git commit -m "CU-01: agrega formulario de registro"

# 4. Antes de subir, traer lo nuevo de develop y resolver conflictos en tu rama
git pull origin develop

# 5. Subir la rama
git push -u origin feature/cu-01-registrar-huesped
```

6. En GitHub, abrir un **Pull Request** de tu rama hacia `develop`.
7. haz merge tu mismo pero revisalo bein que hiciste
8. Se hace el merge y se borra la rama, si no se usara pero la pueden dejar ahi si quieren como historial
9. Cuando un grupo de CU funciona bien junto en `develop`, se abre un Pull Request de `develop` a `main`.

### Mensajes de commit

- Formato: `CU-XX: que se hizo` (por ejemplo `CU-12: recalcula subtotales al cambiar la cantidad`).
- Para la base comun: `base: ...` (por ejemplo `base: agrega menu desplegable a la plantilla`).
- Para correcciones: `fix: ...`.
- En español, en presente y explicando que cambia.

### Si hay conflictos

- Se resuelven **en tu rama**, nunca directamente en `develop` ni en `main`.
- Si el conflicto es en un archivo de otro modulo o en un archivo compartido, se resuelve junto con el dueño de ese archivo.
- Despues de resolverlo, probar que todo siga funcionando antes de subir.

### Lo que no se sube

- Contraseñas o datos reales de conexion a la BD (subir solo un archivo de configuracion de ejemplo).
- Archivos del editor o del sistema, carpetas temporales e imagenes de prueba que no hagan falta (agregarlos al `.gitignore`).

---

## Recomendaciones de trabajo

- **Orden sugerido:** 1) base comun; 2) M1, M2, M3 y M5 al mismo tiempo; 3) M4 y M6; 4) integracion y pruebas finales.
- **Integrar seguido.** Es mejor subir a `develop` un CU terminado cada pocos dias que todo junto al final; asi los conflictos son pequeños.
- **Commits pequeños y frecuentes.** Un commit por cada avance que funcione; si algo se rompe, es facil regresar.
- **Probar el flujo completo** en `develop` cada vez que se une un modulo: visitante → registro → inicio de sesion → catalogo → carrito → pago → disponibilidad actualizada.
- **Probar con los tres actores** (visitante, huesped y administrador) y en al menos dos navegadores.
- **Revisar la hoja de calificacion** antes de cerrar cada modulo para no olvidar ningun punto que se evalua.
- **Reunion corta frecuente** (unos minutos): que hice, que voy a hacer y en que estoy atorado.
- **Datos de prueba compartidos.** Todos usan el mismo script `.sql` para que lo que ve uno sea lo mismo que ven los demas.
- **Pedir ayuda a tiempo.** Si un CU se atora mas de un dia, avisar al equipo para repartir el trabajo o apoyar.