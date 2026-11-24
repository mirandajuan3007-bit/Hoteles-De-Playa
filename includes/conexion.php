<?php
// Conexion a la base de datos. Cada pagina que use la BD lo incluye asi:
//   include_once($raiz . "includes/conexion.php");
// Despues, la variable $conexion ya sirve para mysqli_query().
// Este archivo no es una pagina: si lo abren directo, se manda al inicio.
if (basename($_SERVER["SCRIPT_FILENAME"]) == basename(__FILE__)) {
    header("Location: ../index.php");
    exit;
}

include_once(__DIR__ . "/../config.inc.php");

// XAMPP trae la hora de Europa; se usa la de Mexico para que "hoy" sea correcto.
date_default_timezone_set("America/Mexico_City");

// Los errores se revisan con if en lugar de mostrar avisos de PHP.
mysqli_report(MYSQLI_REPORT_OFF);

$conexion = mysqli_connect($GLOBALS["servidor"], $GLOBALS["usuario"], $GLOBALS["contrasena"], $GLOBALS["base_datos"]);
if (!$conexion) {
    die("No se pudo conectar a la base de datos.");
}
mysqli_set_charset($conexion, "utf8");
?>
