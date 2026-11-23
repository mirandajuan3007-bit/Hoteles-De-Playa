// Validaciones sencillas de los formularios de administración.
function precioValido(valor) {
    if (!/^\d+(\.\d{1,2})?$/.test(valor)) {
        return false;
    }
    var precio = Number(valor);
    return precio >= 0 && precio <= 1000000;
}

function validarDatosHabitacion() {
    var nombre = document.getElementById("nombre").value.trim();
    var categoria = document.getElementById("categoria").value;
    var descripcion = document.getElementById("descripcion").value.trim();
    var precio = document.getElementById("precio").value.trim();
    var disponibles = document.getElementById("disponibles").value.trim();

    if (campoVacio(nombre) || campoVacio(categoria) || campoVacio(descripcion) ||
        campoVacio(precio) || campoVacio(disponibles)) {
        alert("Completa todos los campos.");
        return false;
    }
    if (!precioValido(precio)) {
        alert("El precio debe ser un número entre 0 y 1000000, con hasta dos decimales.");
        return false;
    }
    if (!numeroValido(disponibles, 0, 100000)) {
        alert("La disponibilidad debe ser un número entero entre 0 y 100000.");
        return false;
    }
    return confirmarAccion("¿Guardar los datos de la habitación?");
}

function confirmarEliminacion() {
    return confirmarAccion("¿Eliminar esta habitación del catálogo?");
}
