<?php
// Encabezado y menu de todas las paginas.
// Antes de incluirlo, cada pagina define:
//   $titulo = "Nombre de la pagina";
//   $raiz = "";     (pagina en la carpeta principal)
//   $raiz = "../";  (pagina dentro de una carpeta)

// CU-04: este archivo no es una pagina. Si alguien lo abre directo en el
// navegador (no viene $titulo ni $raiz), lo manda al inicio.
if (!isset($titulo) || !isset($raiz)) {
    header("Location: ../index.php");
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = "";
if (isset($_SESSION["rol"])) {
    $rol = $_SESSION["rol"];
}

// Categorias para el submenu "Habitaciones" (CU-08), leidas de la base de datos.
// Se usa una conexion propia porque algunas paginas ya cerraron la suya.
include_once(__DIR__ . "/../config.inc.php");
mysqli_report(MYSQLI_REPORT_OFF);
$categoriasMenu = array();
$conexionMenu = @mysqli_connect($GLOBALS["servidor"], $GLOBALS["usuario"], $GLOBALS["contrasena"], $GLOBALS["base_datos"]);
if ($conexionMenu) {
    mysqli_set_charset($conexionMenu, "utf8");
    $resultadoMenu = mysqli_query($conexionMenu, "SELECT nombre FROM categorias ORDER BY nombre");
    if ($resultadoMenu) {
        while ($filaMenu = mysqli_fetch_assoc($resultadoMenu)) {
            $categoriasMenu[] = $filaMenu["nombre"];
        }
    }
    mysqli_close($conexionMenu);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- En celulares usa el ancho real de la pantalla -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $titulo; ?> - Hoteles de Playa</title>
    <link rel="stylesheet" href="<?php echo $raiz; ?>css/estilos.css">
</head>
<body>

<header class="encabezado">
    <h1>Hoteles de Playa</h1>
</header>

<nav class="menu">
    <ul>
        <li><a href="<?php echo $raiz; ?>index.php">Inicio</a></li>

        <li>
            <a href="<?php echo $raiz; ?>catalogo/index.php">Habitaciones</a>
            <ul class="submenu">
                <li><a href="<?php echo $raiz; ?>catalogo/index.php">Ver todas</a></li>
                <?php foreach ($categoriasMenu as $categoriaMenu) { ?>
                    <li><a href="<?php echo $raiz; ?>catalogo/index.php?categoria=<?php echo urlencode($categoriaMenu); ?>"><?php echo htmlspecialchars($categoriaMenu, ENT_QUOTES, "UTF-8"); ?></a></li>
                <?php } ?>
            </ul>
        </li>

        <li><a href="<?php echo $raiz; ?>busqueda/index.php">Buscar</a></li>

        <?php if ($rol == "admin") { ?>
            <li>
                <a href="<?php echo $raiz; ?>admin/index.php">Administración</a>
                <ul class="submenu">
                    <li><a href="<?php echo $raiz; ?>admin/index.php">Listado de habitaciones</a></li>
                    <li><a href="<?php echo $raiz; ?>admin/agregar.php">Agregar habitación</a></li>
                </ul>
            </li>
        <?php } ?>

        <?php if ($rol == "huesped" || $rol == "admin") { ?>
            <li><a href="<?php echo $raiz; ?>carrito/index.php">Mi carrito (<span id="contador-carrito">0</span>)</a></li>
        <?php } ?>

        <?php if ($rol == "") { ?>
            <li>
                <a href="<?php echo $raiz; ?>autenticacion/login.php">Mi cuenta</a>
                <ul class="submenu">
                    <li><a href="<?php echo $raiz; ?>autenticacion/login.php">Iniciar sesión</a></li>
                    <li><a href="<?php echo $raiz; ?>autenticacion/registro.php">Registrarse</a></li>
                </ul>
            </li>
        <?php } else { ?>
            <li>
                <a href="#"><?php echo htmlspecialchars($_SESSION["usuario_nombre"], ENT_QUOTES, "UTF-8"); ?></a>
                <ul class="submenu">
                    <li><a href="<?php echo $raiz; ?>autenticacion/cerrar_sesion.php" onclick="return confirm('¿Deseas cerrar la sesión?');">Cerrar sesión</a></li>
                </ul>
            </li>
        <?php } ?>
    </ul>
</nav>

<script>
// CU-11: muestra en el menu cuantas habitaciones hay en la cookie "carrito".
// Si otra pagina cambia el carrito sin recargar, puede llamar actualizarContadorCarrito().
function actualizarContadorCarrito() {
    var contador = document.getElementById("contador-carrito");
    if (!contador) {
        return;
    }
    var total = 0;
    var partes = document.cookie.split("; ");
    for (var i = 0; i < partes.length; i++) {
        if (partes[i].indexOf("carrito=") == 0) {
            try {
                var lista = JSON.parse(decodeURIComponent(partes[i].substring(8)));
                for (var j = 0; j < lista.length; j++) {
                    total = total + Number(lista[j].cantidad);
                }
            } catch (e) {
                total = 0;
            }
        }
    }
    contador.innerHTML = total;
}
actualizarContadorCarrito();
</script>

<main class="contenido">

<?php
// Mensaje que deja otra pagina antes de redirigir (por ejemplo, "Acceso no autorizado").
// Se muestra una sola vez y se borra.
if (isset($_SESSION["mensaje"])) {
?>
    <div class="mensaje-error" role="alert"><?php echo htmlspecialchars($_SESSION["mensaje"], ENT_QUOTES, "UTF-8"); ?></div>
<?php
    unset($_SESSION["mensaje"]);
}
?>
