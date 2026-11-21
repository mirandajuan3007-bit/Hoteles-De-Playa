<?php
// Encabezado y menu de todas las paginas.
// Antes de incluirlo, cada pagina define:
//   $titulo = "Nombre de la pagina";
//   $raiz = "";     (pagina en la carpeta principal)
//   $raiz = "../";  (pagina dentro de una carpeta)

// Solo abre la sesion si la pagina no la abrio antes (por ejemplo, el login
// la abre al inicio para poder redirigir antes de mostrar el HTML).
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$rol = "";
if (isset($_SESSION["rol"])) {
    $rol = $_SESSION["rol"];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
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

        <?php if ($rol == "huesped") { ?>
            <li><a href="<?php echo $raiz; ?>carrito/index.php">Mi carrito</a></li>
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
                <a href="#"><?php echo htmlspecialchars($_SESSION["usuario_nombre"]); ?></a>
                <ul class="submenu">
                    <li><a href="<?php echo $raiz; ?>autenticacion/cerrar_sesion.php">Cerrar sesión</a></li>
                </ul>
            </li>
        <?php } ?>
    </ul>
</nav>

<main class="contenido">
