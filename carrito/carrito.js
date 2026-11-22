// Lee la cookie "carrito" y devuelve la lista de habitaciones.
function leerCarrito() {
    var partes = document.cookie.split("; ");
    for (var i = 0; i < partes.length; i++) {
        if (partes[i].indexOf("carrito=") == 0) {
            return JSON.parse(decodeURIComponent(partes[i].substring(8)));
        }
    }
    return [];
}

// Guarda la lista en la cookie "carrito" por 7 días, disponible en todo el sitio.
function guardarCarrito(lista) {
    var caduca = new Date();
    caduca.setDate(caduca.getDate() + 7);
    var valor = encodeURIComponent(JSON.stringify(lista));
    document.cookie = "carrito=" + valor + "; expires=" + caduca.toUTCString() + "; path=/";
}

// Si el tipo ya está en el carrito, suma la cantidad. Máximo 10 por tipo.
function agregarAlCarrito(id, cantidad) {
    if (!numeroValido(cantidad, 1, 10)) {
        alert("La cantidad debe ser un número entre 1 y 10.");
        return false;
    }

    var lista = leerCarrito();
    var encontrado = false;
    for (var i = 0; i < lista.length; i++) {
        if (lista[i].id == id) {
            var nueva = lista[i].cantidad + Number(cantidad);
            if (!numeroValido(nueva, 1, 10)) {
                alert("Solo puedes reservar hasta 10 habitaciones del mismo tipo.");
                return false;
            }
            lista[i].cantidad = nueva;
            encontrado = true;
        }
    }
    if (!encontrado) {
        lista.push({ id: id, cantidad: Number(cantidad) });
    }

    guardarCarrito(lista);
    return true;
}

// Cambia la cantidad de un tipo de habitación (CU-12). Debe estar entre 1 y 10.
function cambiarCantidad(id, nueva) {
    if (!numeroValido(nueva, 1, 10)) {
        alert("La cantidad debe ser un número entre 1 y 10.");
        return false;
    }

    var lista = leerCarrito();
    for (var i = 0; i < lista.length; i++) {
        if (lista[i].id == id) {
            lista[i].cantidad = Number(nueva);
        }
    }

    guardarCarrito(lista);
    return true;
}

// Quita un tipo de habitación del carrito (CU-13).
function quitarDelCarrito(id) {
    var lista = leerCarrito();
    var nueva = [];
    for (var i = 0; i < lista.length; i++) {
        if (lista[i].id != id) {
            nueva.push(lista[i]);
        }
    }

    guardarCarrito(nueva);
    return true;
}
