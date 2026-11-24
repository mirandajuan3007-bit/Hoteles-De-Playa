// JavaScript del catalogo (CU-08 y CU-09).

// ===== CU-09: ventana emergente con el resumen de la habitacion =====

// Llena la ventana con los datos que tiene la tarjeta y la muestra.
function verDetalles(id) {
    var tarjeta = document.getElementById("hab-" + id);
    if (tarjeta == null) {
        return;
    }

    var nombre = tarjeta.getAttribute("data-nombre");
    var disponibles = Number(tarjeta.getAttribute("data-disponibles"));

    document.getElementById("ventana-nombre").textContent = nombre;
    document.getElementById("ventana-descripcion").textContent = tarjeta.getAttribute("data-descripcion");
    document.getElementById("ventana-precio").textContent = "$" + tarjeta.getAttribute("data-precio");

    if (disponibles > 0) {
        document.getElementById("ventana-disponibles").textContent = "Disponibles: " + disponibles;
    } else {
        document.getElementById("ventana-disponibles").textContent = "Agotada";
    }

    // Si la habitacion no tiene imagen, no se muestra el recuadro de la imagen.
    var imagen = document.getElementById("ventana-imagen");
    var ruta = tarjeta.getAttribute("data-imagen");
    if (ruta == "") {
        imagen.style.display = "none";
    } else {
        imagen.src = ruta;
        imagen.alt = nombre;
        imagen.style.display = "block";
    }

    document.getElementById("ventana").style.display = "flex";
}

function cerrarVentana() {
    document.getElementById("ventana").style.display = "none";
}

// Tambien se cierra con la tecla Esc.
document.onkeydown = function (evento) {
    if (evento.key == "Escape") {
        cerrarVentana();
    }
};

// ===== CU-08: boton "Agregar al carrito" =====
// Usa agregarAlCarrito() y leerCarrito() de carrito/carrito.js
// Este boton solo existe cuando hay una sesion activa, y solo se muestra si la habitacion tiene disponibilidad.

function agregarHabitacion(id, disponibles) {
    // Cuantas habitaciones de este tipo hay ya en el carrito.
    var enCarrito = 0;
    var lista = leerCarrito();
    for (var i = 0; i < lista.length; i++) {
        if (lista[i].id == id) {
            enCarrito = enCarrito + Number(lista[i].cantidad);
        }
    }

    if (enCarrito + 1 > disponibles) {
        alert("Solo hay " + disponibles + " habitaciones disponibles de este tipo.");
        return;
    }

    if (!confirmarAccion("¿Agregar esta habitación al carrito?")) {
        return;
    }

    // Se agrega una habitacion por cada clic. Si la cantidad no es valida,
    // agregarAlCarrito() avisa con su propio mensaje y devuelve false.
    if (agregarAlCarrito(Number(id), 1)) {
        // Esta funcion esta en includes/encabezado.php y actualiza el numero del menu.
        actualizarContadorCarrito();
        mostrarAviso("Habitación agregada al carrito.");
    }
}

// Muestra un mensaje de exito y lo oculta a los 3 segundos.
function mostrarAviso(texto) {
    var aviso = document.getElementById("aviso-carrito");
    aviso.textContent = texto;
    aviso.style.display = "block";
    setTimeout(function () {
        aviso.style.display = "none";
    }, 3000);
}