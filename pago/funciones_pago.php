<?php
// Funciones que usan las paginas del modulo de pago (CU-15).
// Se incluye asi:  require_once("funciones_pago.php");

// CU-04: este archivo no es una pagina. Si alguien lo abre directo, va al inicio.
if (basename($_SERVER["SCRIPT_FILENAME"]) == basename(__FILE__)) {
    header("Location: ../index.php");
    exit;
}

// Escapa un texto antes de mostrarlo en el HTML.
function textoSeguro($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, "UTF-8");
}

// Lee la cookie "carrito" (contrato: [{"id": 3, "cantidad": 2}]) y devuelve
// un arreglo id => cantidad. Si un id viene repetido, se suman las cantidades.
// Una cookie dañada se trata como carrito vacio.
function leerCarritoCookie() {
    $carrito = array();
    if (!isset($_COOKIE["carrito"]) || !is_string($_COOKIE["carrito"])) {
        return $carrito;
    }

    $lista = json_decode($_COOKIE["carrito"], true);
    if (!is_array($lista)) {
        return $carrito;
    }

    foreach ($lista as $elemento) {
        if (!is_array($elemento) || !isset($elemento["id"]) || !isset($elemento["cantidad"])) {
            continue;
        }
        $id = (int)$elemento["id"];
        $cantidad = (int)$elemento["cantidad"];
        if ($id <= 0 || $cantidad <= 0) {
            continue;
        }
        if (!isset($carrito[$id])) {
            $carrito[$id] = 0;
        }
        $carrito[$id] = $carrito[$id] + $cantidad;
    }
    return $carrito;
}

// Trae de la BD el nombre, precio y disponibilidad de cada habitacion del carrito.
// Con $bloquear = true usa FOR UPDATE: las filas quedan apartadas hasta el
// COMMIT, asi dos huespedes no pueden reservar la misma habitacion a la vez.
function cargarHabitacionesCarrito($conexion, $carrito, $bloquear) {
    $habitaciones = array();
    $sql = "SELECT id, nombre, precio, disponibles FROM habitaciones WHERE id = ?";
    if ($bloquear) {
        $sql = $sql . " FOR UPDATE";
    }

    foreach ($carrito as $id => $cantidad) {
        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "i", $id);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_bind_result($consulta, $idGuardado, $nombre, $precio, $disponibles);
        if (mysqli_stmt_fetch($consulta)) {
            $habitaciones[] = array(
                "id" => (int)$idGuardado,
                "nombre" => $nombre,
                "precio" => (float)$precio,
                "disponibles" => (int)$disponibles,
                "cantidad" => (int)$cantidad
            );
        }
        mysqli_stmt_close($consulta);
    }
    return $habitaciones;
}

// Revisa las fechas de la estancia. Devuelve "" si estan bien o el mensaje de error.
function revisarFechas($fechaEntrada, $fechaSalida) {
    if ($fechaEntrada == "" || $fechaSalida == "") {
        return "Selecciona las fechas de entrada y salida.";
    }

    $entrada = DateTime::createFromFormat("!Y-m-d", $fechaEntrada);
    $salida = DateTime::createFromFormat("!Y-m-d", $fechaSalida);
    if ($entrada === false || $salida === false ||
        $entrada->format("Y-m-d") !== $fechaEntrada ||
        $salida->format("Y-m-d") !== $fechaSalida) {
        return "Las fechas no tienen un formato válido.";
    }

    if ($fechaEntrada < date("Y-m-d")) {
        return "La fecha de entrada no puede ser anterior a hoy.";
    }
    if ($salida <= $entrada) {
        return "La fecha de salida debe ser posterior a la fecha de entrada.";
    }
    return "";
}

// Noches entre dos fechas validas (AAAA-MM-DD).
function contarNoches($fechaEntrada, $fechaSalida) {
    $entrada = DateTime::createFromFormat("!Y-m-d", $fechaEntrada);
    $salida = DateTime::createFromFormat("!Y-m-d", $fechaSalida);
    return $entrada->diff($salida)->days;
}
