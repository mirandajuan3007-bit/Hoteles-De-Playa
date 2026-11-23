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

// Devuelve true si el número está entre min y max.
function numeroValido(valor, min, max) {
    var numero = Number(valor);
    if (numero >= min && numero <= max) {
        return true;
    }
    return false;
}

// Pide confirmación al usuario. Devuelve true si acepta, false si cancela.
function confirmarAccion(mensaje) {
    return confirm(mensaje);
}
