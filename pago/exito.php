<?php
// CU-15 Pagar reservacion: pagina que se muestra despues de un pago correcto.
// Recibe el folio en la URL y solo muestra la reservacion
// si pertenece al usuario de la sesion.

include_once("../includes/permisos.php");
permitirSolo(array("huesped", "admin"));

$titulo = "Reservación exitosa";
$raiz = "../";

require_once($raiz . "includes/conexion.php");
require_once("funciones_pago.php");

$folio = 0;
if (isset($_GET["folio"]) && is_string($_GET["folio"]) && ctype_digit($_GET["folio"])) {
    $folio = (int)$_GET["folio"];
}
$usuarioId = (int)$_SESSION["usuario_id"];

$reservacion = null;
$detalle = array();

$consulta = mysqli_prepare($conexion,
    "SELECT fecha_reservacion, fecha_entrada, fecha_salida, total, titular, tarjeta_final
     FROM reservaciones WHERE id_reservacion = ? AND usuario_id = ?");
mysqli_stmt_bind_param($consulta, "ii", $folio, $usuarioId);
mysqli_stmt_execute($consulta);
mysqli_stmt_bind_result($consulta, $fechaReservacion, $fechaEntrada, $fechaSalida, $total, $titular, $tarjetaFinal);
if (mysqli_stmt_fetch($consulta)) {
    $reservacion = array(
        "fecha_reservacion" => $fechaReservacion,
        "fecha_entrada" => $fechaEntrada,
        "fecha_salida" => $fechaSalida,
        "total" => $total,
        "titular" => $titular,
        "tarjeta_final" => $tarjetaFinal
    );
}
mysqli_stmt_close($consulta);

if ($reservacion != null) {
    $consulta = mysqli_prepare($conexion,
        "SELECT h.nombre, d.precio_unitario, d.cantidad, d.subtotal
         FROM detalle_reservacion d
         INNER JOIN habitaciones h ON h.id = d.id_habitacion
         WHERE d.id_reservacion = ?
         ORDER BY h.nombre");
    mysqli_stmt_bind_param($consulta, "i", $folio);
    mysqli_stmt_execute($consulta);
    mysqli_stmt_bind_result($consulta, $nombre, $precio, $cantidad, $subtotal);
    while (mysqli_stmt_fetch($consulta)) {
        $detalle[] = array("nombre" => $nombre, "precio" => $precio, "cantidad" => $cantidad, "subtotal" => $subtotal);
    }
    mysqli_stmt_close($consulta);
}
mysqli_close($conexion);

include($raiz . "includes/encabezado.php");
?>

<link rel="stylesheet" href="pago.css">

<div class="pago-exito">
<?php if ($reservacion == null) { ?>
    <h2>Reservación</h2>
    <div class="mensaje-error" role="alert">No se encontró la reservación.</div>
    <p><a href="<?php echo $raiz; ?>index.php" class="boton">Volver al inicio</a></p>
<?php } else { ?>
    <h2>Reservación exitosa</h2>
    <div class="mensaje-exito" role="status">
        ¡Tu pago se procesó correctamente y tu reservación quedó confirmada!
    </div>

    <div class="pago-datos">
        <p><strong>Folio:</strong> <?php echo $folio; ?></p>
        <p><strong>Fecha de entrada:</strong> <?php echo textoSeguro($reservacion["fecha_entrada"]); ?></p>
        <p><strong>Fecha de salida:</strong> <?php echo textoSeguro($reservacion["fecha_salida"]); ?></p>
        <p><strong>Noches:</strong> <?php echo contarNoches($reservacion["fecha_entrada"], $reservacion["fecha_salida"]); ?></p>
        <p><strong>Titular:</strong> <?php echo textoSeguro($reservacion["titular"]); ?>
           (tarjeta terminada en <?php echo textoSeguro($reservacion["tarjeta_final"]); ?>)</p>
    </div>

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
            <?php foreach ($detalle as $fila) { ?>
                <tr>
                    <td><?php echo textoSeguro($fila["nombre"]); ?></td>
                    <td>$<?php echo number_format($fila["precio"], 2); ?></td>
                    <td><?php echo (int)$fila["cantidad"]; ?></td>
                    <td>$<?php echo number_format($fila["subtotal"], 2); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <p class="pago-total">Total pagado: $<?php echo number_format($reservacion["total"], 2); ?></p>

    <div class="botones">
        <a href="<?php echo $raiz; ?>catalogo/index.php" class="boton">Ver habitaciones</a>
        <a href="<?php echo $raiz; ?>index.php" class="boton boton-claro">Volver al inicio</a>
    </div>

    <script>
    // El carrito ya se vacio en el servidor; esto asegura que la cookie
    // tampoco quede en el navegador y que el contador del menu marque 0.
    document.cookie = "carrito=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
    actualizarContadorCarrito();
    </script>
<?php } ?>
</div>

<?php include($raiz . "includes/pie.php"); ?>
