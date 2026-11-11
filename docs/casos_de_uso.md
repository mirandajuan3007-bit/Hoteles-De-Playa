# casos de uso - Sistema de reservaciones Hoteles de Playa

cada caso de uso es una feature.

Actores:
- Administrador: aceso total, unico que gestiona el catalogo de habitaciones.
- Huesped registrado: aceso a todo excepto la gestion del catalogo.
- Visitante (no registrado): solo puede ver habitaciones y buscar, no puede reservar.

---

## Sesiones

### CU-01 Registrar huesped
*que es:** Un visitante crea su cuenta para poder reservar.
**como se veria:** Opcion "Registrarse" en el menu. Formulario con nombre, correo, usuario y contrasena. Al guardar, muestra mensaje de exito y leva al inicio de sesion.

### CU-02 iniciar sesion
**Que es:** el usuario entra al sistema con su usuario y contrasena; el sistema detecta si es administrador o huesped.
**como se veria:** opcion "iniciar sesion" en el menu. Formulario de usuario y contrasena. Si los datos son incorrectos, muestra un mensje de error en el mismo formulario. Si son correctos, el menu cambia segun el rol.

### cU-03 cerrar sesion
**Que es:** el usuario termina su sesion.
**como se veria:** Opcion "cerrar sesion" en el menu del usuario. Pide confirmacion y regresa a la pagina de inicio como visitante

### cU-04 Restringir aceso a paginas protegida
**Que es:** el sistema bloquea paginas segun el rol (ejempoi. el huesped no entra al catalogo de administracion, el visitante no entra al carrito ni al pago).
**como se veria:** Si alguien abre una pagina sin permiso (aunque escriba la URl directa), lo redirige al inicio de sesion o al inicio con un mensaje de "aceso no autorizado". las opciones sin permiso no aparecen en el menu

---

## catalogo de habitaciones (solo administrador)

### cU-05 Agregar habitacion
**Que es:** el administrador da de alta una habitacion nueva con una o mas imagenes.
**como se veria:** Menu "Administracion > Agregar habitacion". Formulario con datos de la habitacion (nombre, categoria, descripcion, precio, cantidad disponible) y campo para subir una o varias imagenes. Botones "guardar" y "cancelar".

### cU-06 editar habitacion
**que es:** el administrador modifica cualquier dato de una habitacion, incluidas sus imagenes, excepto el identificador.
**como se veria:** en el listado de administracion, boton "editar" en cada habitacion. Abre el formulario con los datos cargados; el identificador se muestra pero no se puede modificar. Permite quitar imagenes actuales y subir nuevas.

### cU-07 eliminar habitacion
**que es:** el administrador borra una habitacion del catalogo.
**como se veria:** Boton "eliminar" en cada habitacion del listado de administracion. Pide confirmacion antes de borrar y luego muestra mensaje de exito.

---

## consulta de habitaciones (todos los usuarios)

### CU-08 listar habitaciones por categoria
**que es:** Muestra las habitaciones registradas agrupadas por categoria, generado desde la base de datos.
**como se veria:** Menu "Habitaciones" con submenu por categoria. cada categoria muestra tarjetas con imagen, nombre, precio y disponibilidad. el huesped ve el boton "Agregar al carrito"; el visitante no.

### CU-09 Ver resumen de habitacion en ventana emergente
**que es:** consultar informacion sintetizada de un tipo de habitacion sin salir de la pagina.
**como se veria:** Boton "Ver detales" en cada tarjeta. Abre una ventana emergente con imagen, descripcion corta, precio y disponibilidad, con boton para cerrarla.

### CU-10 Buscar habitaciones
**que es:** Buscar habitaciones por criterios y mostrar los resultados en formato de reporte.
**como se veria:** Barra o pagina "buscar" en el menu. campos de busqueda (ej. texto, categoria, rango de precio). los resultados salen en una tabla tipo reporte con los datos de cada habitacion encontrada.

---

## carito de reservaciones (huesped, con cookies y JavaScript)

### cU-11 Agregar habitacion al carrito
**Que es:** el huesed agrega un tipo de habitacion a su carrito; se guarda en cookies, no en sesion.
**como se veria:** Boton "Agregar al carrito" en la tarjeta de la habitacion. el icono del carrito en el menu actualiza el numero de habitaciones selecionadas.

### cU-12 editar cantidad en el carrito
**Que es:** el huesped cambia cuantas habitaciones de un mismo tipo quiere reservar.
**como se veria:** en la pagina "Mi carrito", cada fila tiene un campo de cantidad (o botones + / -). Al cambiarla se recalculan los costos al instante.

### cU-13 eliminar habitacion del carrito
**Que es:** el huesped quita un tipo de habitacion del carrito.
**como se veria:** Boton "Quitar" en cada fila del carrito. Pide confirmacion y actualiza la tabla y los totales.

### cU-14 Ver subtotales y total
**Que es:** Mostrar el costo por tipo de habitacion y el costo total de la reservacion.
**como se veria:** Tabla en "Mi carrito" con columnas: habitacion, precio unitario, cantidad, subtotal. Al final, el total de la reservacion y el boton "Pagar".

### cU-15 Pagar reservacion
**Que es:** el huesped confirma y paga la reservacion; despues del pago se descuenta en la base de datos la disponibilidad de las habitaciones reservadas.
**como se veria:** Desde "Pagar", formulario de pago con resumen de la reservacion. Pide confirmacion, procesa el pago, muestra mensaje de reservacion exitosa, vacia el carrito y la disponibilidad mostrada en el catalogo baja.

---

## reglas generales (aplican a todo el sistema)

- Validar con JavaScript todos los campos de todos los formularios.
- Toda acion importante pide confirmar o cancelar antes de continuar.
- Todos los procesos tienen boton "cancelar".
- Menu principal con menus desplegables, visible en todas las paginas (sin calejones sin salida).
- Todas las paginas salen de una misma plantila de diseno.
- Funciona igual en cualquier navegador.
- Imagenes nitidas y colores apropiados.
- Todo el texto de la aplicacion en espanol, sin errores de ortografia.
- regla en git, no debes hacer push directo al main ni a develop
