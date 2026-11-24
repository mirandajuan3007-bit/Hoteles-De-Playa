<?php
// CU-15 Pagar reservacion.
// El huesped revisa el resumen de su carrito, elige las fechas, escribe los datos
// de pago y confirma. Al pagar:
//   1. se descuenta la disponibilidad de cada habitacion en la BD,
//   2. se guarda la reservacion y su detalle,
//   3. se vacia la cookie "carrito" y se muestra la pagina de exito.
// El pago es simulado: no se hace ningun cargo real.

// CU-04: solo entran usuarios con sesion (huesped o administrador), igual que al carrito.
include_once("../includes/permisos.php");
permitirSolo(array("huesped", "admin"));

$titulo = "Pagar reservación";
$raiz = "../";

require_once($raiz . "includes/conexion.php");
require_once("funciones_pago.php");

$error = "";
$fechaEntrada = "";
$fechaSalida = "";
$titular = "";

// Las fechas pueden venir del carrito (boton "Pagar") por GET o del propio formulario por POST.
if (isset($_REQUEST["fecha_entrada"]) && is_string($_REQUEST["fecha_entrada"])) {
    $fechaEntrada = trim($_REQUEST["fecha_entrada"]);
}
if (isset($_REQUEST["fecha_salida"]) && is_string($_REQUEST["fecha_salida"])) {
    $fechaSalida = trim($_REQUEST["fecha_salida"]);
}

$carrito = leerCarritoCookie();

// ===== Procesar el pago =====
// Solo se procesa el pago cuando llegan los datos de la tarjeta. Desde el carrito
// solo llegan las fechas por POST y entonces nada mas se muestra el formulario.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["titular"])) {
    $titular = trim($_POST["titular"] ?? "");
    $tarjeta = str_replace(" ", "", trim($_POST["tarjeta"] ?? ""));
    $vencimiento = trim($_POST["vencimiento"] ?? "");
    $cvv = trim($_POST["cvv"] ?? "");

    // Se valida otra vez en PHP por si el navegador tiene JavaScript apagado.
    $error = revisarFechas($fechaEntrada, $fechaSalida);
    if ($error == "" && count($carrito) == 0) {
        $error = "Tu carrito está vacío.";
    }
    if ($error == "" && !preg_match('/^[\p{L} ]{3,100}$/u', $titular)) {
        $error = "Escribe el nombre del titular como aparece en la tarjeta (solo letras y espacios).";
    }
    if ($error == "" && !preg_match('/^[0-9]{16}$/', $tarjeta)) {
        $error = "El número de tarjeta debe tener 16 dígitos.";
    }
    if ($error == "") {
        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $vencimiento, $partes)) {
            $error = "La fecha de vencimiento debe tener el formato MM/AA.";
        } else {
            $mesVence = (int)$partes[1];
            $anioVence = 2000 + (int)$partes[2];
            $mesActual = (int)date("n");
            $anioActual = (int)date("Y");
            if ($anioVence < $anioActual || ($anioVence == $anioActual && $mesVence < $mesActual)) {
                $error = "La tarjeta está vencida.";
            }
        }
    }
    if ($error == "" && !preg_match('/^[0-9]{3}$/', $cvv)) {
        $error = "El código de seguridad (CVV) debe tener 3 dígitos.";
    }

    if ($error == "") {
        // Todo se hace dentro de una transaccion: si algo falla, no se guarda nada.
        mysqli_begin_transaction($conexion);
        $guardado = true;

        $habitacionesPago = cargarHabitacionesCarrito($conexion, $carrito, true);
        if (count($habitacionesPago) != count($carrito)) {
            $error = "Una de las habitaciones de tu carrito ya no existe. Revisa tu carrito.";
            $guardado = false;
        }

        if ($guardado) {
            foreach ($habitacionesPago as $hab) {
                if ($hab["cantidad"] > $hab["disponibles"]) {
                    $error = "Ya no hay suficientes habitaciones disponibles de \"" . $hab["nombre"] . "\" (quedan " . $hab["disponibles"] . "). Ajusta tu carrito.";
                    $guardado = false;
                    break;
                }
            }
        }

        $noches = contarNoches($fechaEntrada, $fechaSalida);
        $total = 0;
        foreach ($habitacionesPago as $indice => $hab) {
            $subtotal = round($hab["precio"] * $hab["cantidad"] * $noches, 2);
            $habitacionesPago[$indice]["subtotal"] = $subtotal;
            $total = $total + $subtotal;
        }

        // 1. Reservacion. Solo se guardan los ultimos 4 digitos de la tarjeta.
        $idReservacion = 0;
        if ($guardado) {
            $tarjetaFinal = substr($tarjeta, -4);
            $usuarioId = (int)$_SESSION["usuario_id"];
            $consulta = mysqli_prepare($conexion,
                "INSERT INTO reservaciones (usuario_id, fecha_entrada, fecha_salida, total, titular, tarjeta_final)
                 VALUES (?, ?, ?, ?, ?, ?)");
            // Si prepare falla, casi siempre es porque la BD no tiene las columnas nuevas:
            // hay que volver a ejecutar playa.sql.
            if ($consulta) {
                mysqli_stmt_bind_param($consulta, "issdss", $usuarioId, $fechaEntrada, $fechaSalida, $total, $titular, $tarjetaFinal);
                $guardado = mysqli_stmt_execute($consulta);
                $idReservacion = mysqli_insert_id($conexion);
                mysqli_stmt_close($consulta);
            } else {
                $guardado = false;
            }
        }

        // 2. Detalle y descuento de la disponibilidad.
        if ($guardado) {
            foreach ($habitacionesPago as $hab) {
                $consulta = mysqli_prepare($conexion,
                    "INSERT INTO detalle_reservacion (id_reservacion, id_habitacion, cantidad, precio_unitario, subtotal)
                     VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($consulta, "iiidd", $idReservacion, $hab["id"], $hab["cantidad"], $hab["precio"], $hab["subtotal"]);
                $guardado = mysqli_stmt_execute($consulta);
                mysqli_stmt_close($consulta);
                if (!$guardado) {
                    break;
                }

                $consulta = mysqli_prepare($conexion,
                    "UPDATE habitaciones SET disponibles = disponibles - ? WHERE id = ? AND disponibles >= ?");
                mysqli_stmt_bind_param($consulta, "iii", $hab["cantidad"], $hab["id"], $hab["cantidad"]);
                $guardado = mysqli_stmt_execute($consulta) && mysqli_stmt_affected_rows($consulta) == 1;
                mysqli_stmt_close($consulta);
                if (!$guardado) {
                    break;
                }
            }
        }

        if ($guardado && mysqli_commit($conexion)) {
            mysqli_close($conexion);
            // Se vacia el carrito: la cookie se borra con la misma ruta "/" con la que se creo.
            setcookie("carrito", "", time() - 3600, "/");
            header("Location: exito.php?folio=" . $idReservacion);
            exit;
        }

        mysqli_rollback($conexion);
        if ($error == "") {
            $error = "No se pudo procesar el pago. Intenta de nuevo.";
        }
    }
}

// ===== Datos para mostrar el resumen =====
$habitaciones = cargarHabitacionesCarrito($conexion, $carrito, false);
mysqli_close($conexion);

$excedeDisponibles = false;
foreach ($habitaciones as $hab) {
    if ($hab["cantidad"] > $hab["disponibles"]) {
        $excedeDisponibles = true;
    }
}

// Si las fechas que llegaron son validas, el resumen ya sale calculado.
$noches = 0;
if ($fechaEntrada != "" && $fechaSalida != "" && revisarFechas($fechaEntrada, $fechaSalida) == "") {
    $noches = contarNoches($fechaEntrada, $fechaSalida);
}
$total = 0;
foreach ($habitaciones as $indice => $hab) {
    $habitaciones[$indice]["subtotal"] = round($hab["precio"] * $hab["cantidad"] * $noches, 2);
    $total = $total + $habitaciones[$indice]["subtotal"];
}

include($raiz . "includes/encabezado.php");
?>

<link rel="stylesheet" href="pago.css">

<h2>Pagar reservación</h2>

<?php if ($error != "") { ?>
    <div class="mensaje-error" role="alert"><?php echo textoSeguro($error); ?></div>
<?php } ?>

<?php if (count($habitaciones) == 0) { ?>
    <p>Tu carrito está vacío. Agrega habitaciones antes de pagar.</p>
    <p><a href="<?php echo $raiz; ?>catalogo/index.php" class="boton">Ver habitaciones</a></p>

<?php } elseif ($excedeDisponibles) { ?>
    <div class="mensaje-error" role="alert">
        Una o más habitaciones de tu carrito superan la cantidad disponible. Ajusta la cantidad antes de pagar.
    </div>
    <p><a href="<?php echo $raiz; ?>carrito/index.php" class="boton">Volver al carrito</a></p>

<?php } else { ?>
    <div class="pago">
        <!-- Resumen de la reservacion -->
        <section class="pago-resumen">
            <h3>Resumen de la reservación</h3>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Habitación</th>
                        <th>Precio por noche</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($habitaciones as $hab) { ?>
                        <tr class="fila-pago" data-id="<?php echo $hab["id"]; ?>"
                            data-precio="<?php echo $hab["precio"]; ?>" data-cantidad="<?php echo $hab["cantidad"]; ?>">
                            <td><?php echo textoSeguro($hab["nombre"]); ?></td>
                            <td>$<?php echo number_format($hab["precio"], 2); ?></td>
                            <td><?php echo $hab["cantidad"]; ?></td>
                            <td id="subtotal-<?php echo $hab["id"]; ?>">
                                <?php if ($noches > 0) { ?>
                                    $<?php echo number_format($hab["subtotal"], 2); ?>
                                <?php } else { ?>
                                    Elige las fechas
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p>Noches: <strong id="pago-noches"><?php echo $noches; ?></strong></p>
            <p class="pago-total">Total a pagar: $<span id="pago-total"><?php echo number_format($total, 2); ?></span></p>
            <p><a href="<?php echo $raiz; ?>carrito/index.php">Modificar mi carrito</a></p>
        </section>

        <!-- Formulario de pago. novalidate: la validacion la hace JavaScript (validarPago). -->
        <section class="pago-formulario">
            <form class="formulario" id="formulario-pago" method="post" action="index.php"
                  onsubmit="return validarPago();" novalidate>
                <div id="error-pago" class="mensaje-error" role="alert" style="display: none;"></div>

                <fieldset>
                    <legend>Fechas de la estancia</legend>
                    <label for="fecha_entrada">Fecha de entrada</label>
                    <input type="date" id="fecha_entrada" name="fecha_entrada" min="<?php echo date("Y-m-d"); ?>"
                           value="<?php echo textoSeguro($fechaEntrada); ?>" onchange="recalcularResumen()">

                    <label for="fecha_salida">Fecha de salida</label>
                    <input type="date" id="fecha_salida" name="fecha_salida" min="<?php echo date("Y-m-d"); ?>"
                           value="<?php echo textoSeguro($fechaSalida); ?>" onchange="recalcularResumen()">
                </fieldset>

                <fieldset>
                    <legend>Datos de la tarjeta</legend>
                    <label for="titular">Nombre del titular</label>
                    <input type="text" id="titular" name="titular" maxlength="100" autocomplete="cc-name"
                           value="<?php echo textoSeguro($titular); ?>">

                    <label for="tarjeta">Número de tarjeta</label>
                    <input type="text" id="tarjeta" name="tarjeta" maxlength="19" inputmode="numeric"
                           autocomplete="cc-number" placeholder="1234 5678 9012 3456">

                    <div class="pago-fila">
                        <div>
                            <label for="vencimiento">Vencimiento</label>
                            <input type="text" id="vencimiento" name="vencimiento" maxlength="5"
                                   autocomplete="cc-exp" placeholder="MM/AA">
                        </div>
                        <div>
                            <label for="cvv">CVV</label>
                            <input type="password" id="cvv" name="cvv" maxlength="3" inputmode="numeric"
                                   autocomplete="cc-csc" placeholder="123">
                        </div>
                    </div>
                    <p class="pago-nota">Pago simulado para el proyecto.</p>
                </fieldset>

                <div class="botones">
                    <button type="submit" class="boton">Pagar</button>
                    <a href="<?php echo $raiz; ?>carrito/index.php" class="boton boton-cancelar"
                       onclick="return confirmarAccion('¿Cancelar el pago y volver al carrito?');">Cancelar</a>
                </div>
            </form>
        </section>
    </div>

    <!-- Primero validaciones.js (funciones comunes) y despues pago.js, que las usa. -->
    <script src="<?php echo $raiz; ?>js/validaciones.js"></script>
    <script src="pago.js"></script>
<?php } ?>

<?php include($raiz . "includes/pie.php"); ?>