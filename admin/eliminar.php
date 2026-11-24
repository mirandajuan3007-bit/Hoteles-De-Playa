<?php
include_once("../includes/permisos.php");
permitirSolo(array("admin"));

$titulo = "Eliminar habitación";
$raiz = "../";
$error = "";
$idHabitacion = 0;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idHabitacion = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
} elseif (isset($_GET["id"])) {
    $idHabitacion = (int)$_GET["id"];
}

if ($idHabitacion <= 0) {
    header("Location: index.php");
    exit;
}

require_once($raiz . "includes/conexion.php");
require_once("funciones.php");

$consulta = mysqli_prepare($conexion, "SELECT nombre FROM habitaciones WHERE id = ?");
mysqli_stmt_bind_param($consulta, "i", $idHabitacion);
mysqli_stmt_execute($consulta);
mysqli_stmt_bind_result($consulta, $nombre);
$encontrada = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);

if (!$encontrada) {
    mysqli_close($conexion);
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $imagenes = obtenerImagenesHabitacion($conexion, $idHabitacion);
    $consulta = mysqli_prepare($conexion, "DELETE FROM habitaciones WHERE id = ?");
    mysqli_stmt_bind_param($consulta, "i", $idHabitacion);
    $eliminada = mysqli_stmt_execute($consulta);
    mysqli_stmt_close($consulta);

    if ($eliminada) {
        foreach ($imagenes as $imagen) {
            borrarArchivoHabitacion($imagen["ruta"]);
        }
        mysqli_close($conexion);
        header("Location: index.php?eliminada=1");
        exit;
    }

    $error = "No se pudo eliminar. La habitación puede estar asociada a una reservación.";
}

mysqli_close($conexion);
include($raiz . "includes/encabezado.php");
?>

<link rel="stylesheet" href="admin.css">
<h2>Eliminar habitación</h2>
<?php if ($error !== "") { ?>
    <div class="mensaje-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></div>
<?php } ?>
<p>¿Quieres eliminar la habitación “<?php echo htmlspecialchars($nombre, ENT_QUOTES, "UTF-8"); ?>”?</p>
<form method="post" action="eliminar.php" onsubmit="return confirmarEliminacion();">
    <input type="hidden" name="id" value="<?php echo $idHabitacion; ?>">
    <button type="submit" class="boton boton-cancelar">Eliminar</button>
    <a href="index.php" class="boton">Cancelar</a>
</form>
<script src="<?php echo $raiz; ?>js/validaciones.js"></script>
<script src="admin.js"></script>
<?php include($raiz . "includes/pie.php"); ?>
