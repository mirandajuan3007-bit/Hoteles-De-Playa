<?php
// CU-04: solo entran usuarios con sesion (huesped o administrador).
include_once("../includes/permisos.php");
permitirSolo(array("huesped", "admin"));

$titulo = "Mi carrito";
$raiz = "../";
$mensaje = "";
$noches = 0;
$total = 0;
$subtotalCalculado = false;
$fechaEntrada = "";
$fechaSalida = "";
$habitaciones = array();

require_once($raiz . "includes/conexion.php");

$elementosCarrito = array();
if (isset($_COOKIE["carrito"]) && is_string($_COOKIE["carrito"])) {
    $carritoDecodificado = json_decode($_COOKIE["carrito"], true);
    if (is_array($carritoDecodificado)) {
        $elementosCarrito = $carritoDecodificado;
    }
}

foreach ($elementosCarrito as $elemento) {
    if (!is_array($elemento) || !isset($elemento["id"]) || !isset($elemento["cantidad"])) {
        continue;
    }

    $idHabitacion = (int)$elemento["id"];
    $cantidad = (int)$elemento["cantidad"];
    if ($idHabitacion <= 0 || $cantidad <= 0) {
        continue;
    }

    $consulta = mysqli_prepare(
        $conexion,
        "SELECT id, nombre, precio, disponibles FROM habitaciones WHERE id = ?"
    );
    mysqli_stmt_bind_param($consulta, "i", $idHabitacion);
    mysqli_stmt_execute($consulta);
    mysqli_stmt_bind_result($consulta, $idGuardado, $nombre, $precio, $disponibles);

    if (mysqli_stmt_fetch($consulta)) {
        $habitaciones[] = array(
            "id" => $idGuardado,
            "nombre" => $nombre,
            "precio" => $precio,
            "disponibles" => $disponibles,
            "cantidad" => $cantidad,
            "subtotal" => 0
        );
    }

    mysqli_stmt_close($consulta);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fechaEntrada = trim($_POST["fecha_entrada"] ?? "");
    $fechaSalida = trim($_POST["fecha_salida"] ?? "");

    $entrada = DateTime::createFromFormat("!Y-m-d", $fechaEntrada);
    $salida = DateTime::createFromFormat("!Y-m-d", $fechaSalida);

    if (count($habitaciones) === 0) {
        $mensaje = "Tu carrito está vacío.";
    } elseif (
        $entrada === false || $salida === false ||
        $entrada->format("Y-m-d") !== $fechaEntrada ||
        $salida->format("Y-m-d") !== $fechaSalida ||
        $salida <= $entrada
    ) {
        $mensaje = "La fecha de salida debe ser posterior a la fecha de entrada.";
    } else {
        $noches = $entrada->diff($salida)->days;

        foreach ($habitaciones as $habitacion) {
            if ($habitacion["cantidad"] > $habitacion["disponibles"]) {
                $mensaje = "La cantidad seleccionada supera la disponibilidad de una habitación.";
                break;
            }
        }

        if ($mensaje === "") {
            foreach ($habitaciones as $indice => $habitacion) {
                $subtotal = round($habitacion["precio"] * $habitacion["cantidad"] * $noches, 2);
                $habitaciones[$indice]["subtotal"] = $subtotal;
                $total += $subtotal;
            }
            $subtotalCalculado = true;
        }
    }
}

mysqli_close($conexion);
include($raiz . "includes/encabezado.php");
?>

<h2>Mi carrito</h2>

<script src="../js/validaciones.js"></script>
<script src="carrito.js"></script>
<script>
function validarFechas() {
    var fechaEntrada = document.getElementById("fecha_entrada").value;
    var fechaSalida = document.getElementById("fecha_salida").value;

    if (campoVacio(fechaEntrada) || campoVacio(fechaSalida)) {
        alert("Selecciona las fechas de entrada y salida.");
        return false;
    }
    if (fechaSalida <= fechaEntrada) {
        alert("La fecha de salida debe ser posterior a la fecha de entrada.");
        return false;
    }
    return true;
}

function actualizarCantidad(id) {
    var nueva = document.getElementById("cant-" + id).value;
    if (!numeroValido(nueva, 1, 10)) {
        alert("La cantidad debe ser un número entero entre 1 y 10.");
        return;
    }
    if (!confirmarAccion("¿Cambiar la cantidad de esta habitación?")) {
        return;
    }
    cambiarCantidad(id, nueva);
    recalcularTotales();
}

function pedirQuitar(id) {
    if (!confirmarAccion("¿Quitar esta habitación del carrito?")) {
        return;
    }
    quitarDelCarrito(id);
    var fila = document.getElementById("fila-" + id);
    fila.parentNode.removeChild(fila);
    if (contarFilas() == 0) {
        window.location.href = "index.php";
        return;
    }
    recalcularTotales();
}
</script>

<?php if ($mensaje !== "") { ?>
    <p role="alert"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<?php if (count($habitaciones) === 0) { ?>
    <p>Tu carrito está vacío.</p>
    <p><a href="../index.php">Volver al inicio</a></p>
<?php } else { ?>
    <form method="post" action="index.php" onsubmit="return validarFechas();">
        <p>
            <label for="fecha_entrada">Fecha de entrada:</label><br>
            <input type="date" id="fecha_entrada" name="fecha_entrada" required
                   value="<?php echo htmlspecialchars($fechaEntrada, ENT_QUOTES, "UTF-8"); ?>">
        </p>
        <p>
            <label for="fecha_salida">Fecha de salida:</label><br>
            <input type="date" id="fecha_salida" name="fecha_salida" required
                   value="<?php echo htmlspecialchars($fechaSalida, ENT_QUOTES, "UTF-8"); ?>">
        </p>
        <button type="submit">Calcular subtotales</button>
        <a href="../index.php">Cancelar</a>
    </form>

    <?php if ($subtotalCalculado) { ?>
        <h3>Resumen de estancia</h3>
        <p>
            Entrada: <?php echo htmlspecialchars($fechaEntrada, ENT_QUOTES, "UTF-8"); ?>;
            salida: <?php echo htmlspecialchars($fechaSalida, ENT_QUOTES, "UTF-8"); ?>;
            noches: <?php echo $noches; ?>.
        </p>
    <?php } ?>

    <table>
        <thead>
            <tr>
                <th>Habitación</th>
                <th>Precio por noche</th>
                <th>Cantidad</th>
                <?php if ($subtotalCalculado) { ?><th>Noches</th><?php } ?>
                <th>Subtotal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($habitaciones as $habitacion) { ?>
                <tr id="fila-<?php echo (int)$habitacion["id"]; ?>" data-id="<?php echo (int)$habitacion["id"]; ?>" data-precio="<?php echo (float)$habitacion["precio"]; ?>">
                    <td><?php echo htmlspecialchars($habitacion["nombre"], ENT_QUOTES, "UTF-8"); ?></td>
                    <td>$<?php echo number_format($habitacion["precio"], 2); ?></td>
                    <td><?php echo (int)$habitacion["cantidad"]; ?></td>
                    <?php if ($subtotalCalculado) { ?><td><?php echo $noches; ?></td><?php } ?>
                    <td id="sub-<?php echo (int)$habitacion["id"]; ?>">
                        <?php if ($subtotalCalculado) { ?>
                            $<?php echo number_format($habitacion["subtotal"], 2); ?>
                        <?php } else { ?>
                            Se calcula al elegir las fechas
                        <?php } ?>
                    </td>
                    <td>
                        <input type="number" id="cant-<?php echo (int)$habitacion["id"]; ?>" min="1" max="10" oninput="recalcularTotales()"
                               value="<?php echo (int)$habitacion["cantidad"]; ?>">
                        <button type="button" onclick="actualizarCantidad(<?php echo (int)$habitacion["id"]; ?>)">Actualizar</button>
                        <button type="button" onclick="pedirQuitar(<?php echo (int)$habitacion["id"]; ?>)">Quitar</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3>Total: $<span id="total"><?php echo number_format($total, 2); ?></span></h3>
<?php } ?>

<?php include($raiz . "includes/pie.php"); ?>
