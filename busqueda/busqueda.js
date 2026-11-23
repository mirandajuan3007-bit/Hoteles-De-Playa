// JavaScript de la busqueda (CU-10).
// Usa campoVacio() de js/validaciones.js, que se carga antes.

function precioValido(valor) {
    if (!/^\d+(\.\d{1,2})?$/.test(valor)) {
        return false;
    }
    var precio = Number(valor);
    return precio >= 0 && precio <= 1000000;
}

// Se llama al enviar el formulario. Si devuelve false, no se envia.
function validarBusqueda() {
    var texto = document.getElementById("texto").value.trim();
    var minimo = document.getElementById("precio_min").value.trim();
    var maximo = document.getElementById("precio_max").value.trim();

    if (texto.length > 100) {
        alert("El texto de búsqueda no puede pasar de 100 caracteres.");
        return false;
    }

    // Los precios admiten centavos; numeroValido() solo acepta enteros.
    if (!campoVacio(minimo) && !precioValido(minimo)) {
        alert("El precio mínimo debe ser un número de hasta dos decimales entre 0 y 1000000.");
        return false;
    }
    if (!campoVacio(maximo) && !precioValido(maximo)) {
        alert("El precio máximo debe ser un número de hasta dos decimales entre 0 y 1000000.");
        return false;
    }
    if (!campoVacio(minimo) && !campoVacio(maximo) && Number(minimo) > Number(maximo)) {
        alert("El precio mínimo no puede ser mayor que el precio máximo.");
        return false;
    }

    return true;
}
