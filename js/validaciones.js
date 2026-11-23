// Validaciones comunes para todos los formularios.

// Devuelve true si el campo está vacío.
function campoVacio(valor) {
    if (valor == "") {
        return true;
    }
    return false;
}

// Devuelve true si el correo tiene "@" y un punto.
function correoValido(correo) {
    if (correo.indexOf("@") > 0 && correo.indexOf(".") > 0) {
        return true;
    }
    return false;
}

// Devuelve true si el número está entre min y max (incluidos).
function numeroValido(valor, min, max) {
    var texto = String(valor);
    if (texto == "") {
        return false;
    }
    // Solo se aceptan dígitos del 0 al 9: sin punto, sin espacios, sin letras.
    for (var i = 0; i < texto.length; i++) {
        var letra = texto.charAt(i);
        if (letra < "0" || letra > "9") {
            return false;
        }
    }
    var numero = Number(texto);
    if (numero >= min && numero <= max) {
        return true;
    }
    return false;
}

// Pide confirmación al usuario. Devuelve true si acepta, false si cancela.
function confirmarAccion(mensaje) {
    return confirm(mensaje);
}
