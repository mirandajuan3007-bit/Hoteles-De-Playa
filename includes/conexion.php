<?php
// Conexion a la base de datos. Cada pagina que use la BD lo incluye asi:
//   include_once($raiz . "includes/conexion.php");
// Despues, la variable $conexion ya sirve para mysqli_query().
include_once(__DIR__ . "/../config.inc.php");

// Los errores se revisan con if en lugar de mostrar avisos de PHP.
mysqli_report(MYSQLI_REPORT_OFF);

$conexion = mysqli_connect($GLOBALS["servidor"], $GLOBALS["usuario"], $GLOBALS["contrasena"], $GLOBALS["base_datos"]);
if (!$conexion) {
    die("No se pudo conectar a la base de datos.");
}
mysqli_set_charset($conexion, "utf8");
?>
