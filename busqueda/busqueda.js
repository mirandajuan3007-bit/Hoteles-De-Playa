// JavaScript de la busqueda (CU-10).
// Usa campoVacio() y numeroValido() de js/validaciones.js, que se carga antes.

// Se llama al enviar el formulario. Si devuelve false, no se envia.
function validarBusqueda() {
    var texto = document.getElementById("texto").value.trim();
    var minimo = document.getElementById("precio_min").value.trim();
    var maximo = document.getElementById("precio_max").value.trim();

    if (texto.length > 100) {
        alert("El texto de búsqueda no puede pasar de 100 caracteres.");
        return false;
    }

    // Los precios son opcionales, pero si se escriben deben ser enteros.
    if (!campoVacio(minimo) && !numeroValido(minimo, 0, 1000000)) {
        alert("El precio mínimo debe ser un número entero mayor o igual a 0.");
        return false;
    }
    if (!campoVacio(maximo) && !numeroValido(maximo, 0, 1000000)) {
        alert("El precio máximo debe ser un número entero mayor o igual a 0.");
        return false;
    }
    if (!campoVacio(minimo) && !campoVacio(maximo) && Number(minimo) > Number(maximo)) {
        alert("El precio mínimo no puede ser mayor que el precio máximo.");
        return false;
    }

    return true;
}