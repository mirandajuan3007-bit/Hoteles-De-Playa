// JavaScript del pago (CU-15).
// Usa campoVacio() y confirmarAccion() de js/validaciones.js,
// por eso validaciones.js se carga antes que este archivo.

// Devuelve true si el texto tiene solo digitos del 0 al 9 y exactamente "largo" caracteres.
function digitosExactos(texto, largo) {
    if (texto.length != largo) {
        return false;
    }
    for (var i = 0; i < texto.length; i++) {
        var letra = texto.charAt(i);
        if (letra < "0" || letra > "9") {
            return false;
        }
    }
    return true;
}

// Fecha de hoy en formato AAAA-MM-DD (el mismo que usan los campos de fecha).
function fechaDeHoy() {
    var hoy = new Date();
    var mes = String(hoy.getMonth() + 1);
    var dia = String(hoy.getDate());
    if (mes.length == 1) {
        mes = "0" + mes;
    }
    if (dia.length == 1) {
        dia = "0" + dia;
    }
    return hoy.getFullYear() + "-" + mes + "-" + dia;
}

// Noches entre la fecha de entrada y la de salida. Devuelve 0 si no son validas.
function nochesDelPago() {
    var entrada = document.getElementById("fecha_entrada").value;
    var salida = document.getElementById("fecha_salida").value;
    if (campoVacio(entrada) || campoVacio(salida)) {
        return 0;
    }
    // Se usa Date.UTC para que el cambio de horario no altere la cuenta.
    var e = entrada.split("-");
    var s = salida.split("-");
    var dias = (Date.UTC(s[0], s[1] - 1, s[2]) - Date.UTC(e[0], e[1] - 1, e[2])) / 86400000;
    if (dias > 0) {
        return dias;
    }
    return 0;
}

// Da formato de dinero: 1234.5 -> "1,234.50" (igual que number_format de PHP).
function formatoDinero(cantidad) {
    var partes = cantidad.toFixed(2).split(".");
    var entero = partes[0];
    var conComas = "";
    while (entero.length > 3) {
        conComas = "," + entero.substring(entero.length - 3) + conComas;
        entero = entero.substring(0, entero.length - 3);
    }
    return entero + conComas + "." + partes[1];
}

// Calcula el total con las fechas elegidas. Tambien actualiza el resumen en pantalla.
function recalcularResumen() {
    var noches = nochesDelPago();
    var total = 0;
    var filas = document.getElementsByTagName("tr");
    for (var i = 0; i < filas.length; i++) {
        var id = filas[i].getAttribute("data-id");
        if (id == null) {
            continue;
        }
        var precio = Number(filas[i].getAttribute("data-precio"));
        var cantidad = Number(filas[i].getAttribute("data-cantidad"));
        var subtotal = precio * cantidad * noches;
        total = total + subtotal;

        var celda = document.getElementById("subtotal-" + id);
        if (noches > 0) {
            celda.innerHTML = "$" + formatoDinero(subtotal);
        } else {
            celda.innerHTML = "Elige las fechas";
        }
    }
    document.getElementById("pago-noches").innerHTML = noches;
    document.getElementById("pago-total").innerHTML = formatoDinero(total);
    return total;
}

// Muestra el error arriba del formulario y pone el cursor en el campo con problema.
function mostrarErrorPago(mensaje, idCampo) {
    var caja = document.getElementById("error-pago");
    caja.textContent = mensaje;
    caja.style.display = "block";
    document.getElementById(idCampo).focus();
    return false;
}

// Valida todo el formulario y pide confirmacion antes de pagar.
function validarPago() {
    var entrada = document.getElementById("fecha_entrada").value;
    var salida = document.getElementById("fecha_salida").value;
    var titular = document.getElementById("titular").value.trim();
    var tarjeta = document.getElementById("tarjeta").value.split(" ").join("");
    var vencimiento = document.getElementById("vencimiento").value.trim();
    var cvv = document.getElementById("cvv").value.trim();

    document.getElementById("error-pago").style.display = "none";

    // Fechas
    if (campoVacio(entrada)) {
        return mostrarErrorPago("Selecciona la fecha de entrada.", "fecha_entrada");
    }
    if (campoVacio(salida)) {
        return mostrarErrorPago("Selecciona la fecha de salida.", "fecha_salida");
    }
    if (entrada < fechaDeHoy()) {
        return mostrarErrorPago("La fecha de entrada no puede ser anterior a hoy.", "fecha_entrada");
    }
    if (salida <= entrada) {
        return mostrarErrorPago("La fecha de salida debe ser posterior a la fecha de entrada.", "fecha_salida");
    }

    // Titular: solo letras (con acentos) y espacios
    if (campoVacio(titular)) {
        return mostrarErrorPago("Escribe el nombre del titular de la tarjeta.", "titular");
    }
    if (titular.length < 3 || !/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü ]+$/.test(titular)) {
        return mostrarErrorPago("El nombre del titular solo puede tener letras y espacios (mínimo 3).", "titular");
    }

    // Tarjeta: 16 digitos (se aceptan espacios entre grupos)
    if (campoVacio(tarjeta)) {
        return mostrarErrorPago("Escribe el número de tarjeta.", "tarjeta");
    }
    if (!digitosExactos(tarjeta, 16)) {
        return mostrarErrorPago("El número de tarjeta debe tener 16 dígitos.", "tarjeta");
    }

    // Vencimiento: MM/AA y que no este vencida
    if (!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(vencimiento)) {
        return mostrarErrorPago("La fecha de vencimiento debe tener el formato MM/AA.", "vencimiento");
    }
    var mes = Number(vencimiento.substring(0, 2));
    var anio = 2000 + Number(vencimiento.substring(3, 5));
    var hoy = new Date();
    if (anio < hoy.getFullYear() || (anio == hoy.getFullYear() && mes < hoy.getMonth() + 1)) {
        return mostrarErrorPago("La tarjeta está vencida.", "vencimiento");
    }

    // CVV: 3 digitos
    if (!digitosExactos(cvv, 3)) {
        return mostrarErrorPago("El código de seguridad (CVV) debe tener 3 dígitos.", "cvv");
    }

    // Confirmar o cancelar antes de pagar (regla general del proyecto).
    var total = recalcularResumen();
    var noches = nochesDelPago();
    var textoNoches = noches == 1 ? "1 noche" : noches + " noches";
    return confirmarAccion("¿Confirmas el pago de $" + formatoDinero(total) + " por " + textoNoches + "?");
}

// Al abrir la pagina, si ya hay fechas (vienen del carrito), se calcula el resumen.
recalcularResumen();
