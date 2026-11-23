<?php
// CU-08 Listar habitaciones por categoria y CU-09 Ver resumen en ventana emergente.
// Esta pagina la puede ver cualquier usuario (visitante, huesped o administrador),
// por eso NO incluye permisos.php.
$titulo = "Habitaciones";
$raiz = "../";

require_once($raiz . "includes/conexion.php");

// El submenu "Habitaciones" manda la categoria en la URL:
//   catalogo/index.php?categoria=Deluxe
// Sin categoria se muestran todas.
$categoriaElegida = "";
if (isset($_GET["categoria"]) && is_string($_GET["categoria"])) {
    $categoriaElegida = trim($_GET["categoria"]);
}

// Se trae la primera imagen de cada habitacion (la tabla imagenes puede tener varias).
$sql = "SELECT h.id, h.nombre, h.descripcion, h.precio, h.disponibles, h.categoria, c.descripcion,
               (SELECT i.ruta FROM imagenes i WHERE i.id_habitacion = h.id ORDER BY i.id_imagen LIMIT 1)
        FROM habitaciones h
        INNER JOIN categorias c ON c.nombre = h.categoria";
if ($categoriaElegida != "") {
    $sql = $sql . " WHERE h.categoria = ?";
}
$sql = $sql . " ORDER BY h.categoria, h.nombre";

// Consulta preparada: la categoria de la URL nunca se pega directo en el SQL.
$consulta = mysqli_prepare($conexion, $sql);
if ($categoriaElegida != "") {
    mysqli_stmt_bind_param($consulta, "s", $categoriaElegida);
}
mysqli_stmt_execute($consulta);
mysqli_stmt_bind_result($consulta, $idHab, $nombreHab, $descripcionHab, $precioHab, $disponiblesHab, $categoriaHab, $descripcionCategoria, $rutaImagen);

// Se agrupa por categoria:
//   $categorias["Deluxe"]["descripcion"]  texto de la categoria
//   $categorias["Deluxe"]["habitaciones"] lista de habitaciones
$categorias = array();
while (mysqli_stmt_fetch($consulta)) {
    if (!isset($categorias[$categoriaHab])) {
        $categorias[$categoriaHab] = array(
            "descripcion" => $descripcionCategoria,
            "habitaciones" => array()
        );
    }

    // Si la habitacion no tiene imagen, o el archivo no existe, la tarjeta dice "Sin imagen".
    $imagen = "";
    if ($rutaImagen != "" && file_exists(__DIR__ . "/../" . $rutaImagen)) {
        $imagen = $raiz . $rutaImagen;
    }

    $categorias[$categoriaHab]["habitaciones"][] = array(
        "id" => $idHab,
        "nombre" => $nombreHab,
        "descripcion" => $descripcionHab,
        "precio" => $precioHab,
        "disponibles" => $disponiblesHab,
        "imagen" => $imagen
    );
}
mysqli_stmt_close($consulta);
mysqli_close($conexion);

// El encabezado define $rol ("" si es visitante, "huesped" o "admin").
include($raiz . "includes/encabezado.php");
?>

<link rel="stylesheet" href="catalogo.css">

<h2>
    Habitaciones<?php if ($categoriaElegida != "") { ?>: <?php echo htmlspecialchars($categoriaElegida, ENT_QUOTES, "UTF-8"); ?><?php } ?>
</h2>

<?php if ($categoriaElegida != "") { ?>
    <p><a href="index.php">Ver todas las categorías</a></p>
<?php } ?>

<?php if ($rol == "") { ?>
    <!-- Visitante: puede ver las habitaciones, pero no reservar -->
    <p class="aviso-visitante">
        Para reservar necesitas
        <a href="<?php echo $raiz; ?>autenticacion/login.php">iniciar sesión</a> o
        <a href="<?php echo $raiz; ?>autenticacion/registro.php">registrarte</a>.
    </p>
<?php } ?>

<!-- Aviso que aparece unos segundos al agregar una habitacion al carrito -->
<div id="aviso-carrito" class="mensaje-exito" role="status" style="display: none;"></div>

<?php if (count($categorias) == 0) { ?>
    <p>No hay habitaciones en esta categoría por el momento.</p>
<?php } ?>

<?php foreach ($categorias as $nombreCategoria => $datosCategoria) { ?>
    <section class="categoria">
        <h3><?php echo htmlspecialchars((string)$nombreCategoria, ENT_QUOTES, "UTF-8"); ?></h3>
        <p><?php echo htmlspecialchars($datosCategoria["descripcion"], ENT_QUOTES, "UTF-8"); ?></p>

        <div class="tarjetas">
            <?php foreach ($datosCategoria["habitaciones"] as $hab) { ?>
                <!-- Los data-... guardan lo que necesita la ventana emergente (CU-09) -->
                <article class="tarjeta" id="hab-<?php echo (int)$hab["id"]; ?>"
                         data-nombre="<?php echo htmlspecialchars($hab["nombre"], ENT_QUOTES, "UTF-8"); ?>"
                         data-descripcion="<?php echo htmlspecialchars($hab["descripcion"], ENT_QUOTES, "UTF-8"); ?>"
                         data-precio="<?php echo number_format($hab["precio"], 2); ?>"
                         data-disponibles="<?php echo (int)$hab["disponibles"]; ?>"
                         data-imagen="<?php echo htmlspecialchars($hab["imagen"], ENT_QUOTES, "UTF-8"); ?>">

                    <?php if ($hab["imagen"] != "") { ?>
                        <img src="<?php echo htmlspecialchars($hab["imagen"], ENT_QUOTES, "UTF-8"); ?>"
                             alt="<?php echo htmlspecialchars($hab["nombre"], ENT_QUOTES, "UTF-8"); ?>">
                    <?php } else { ?>
                        <div class="sin-imagen">Sin imagen</div>
                    <?php } ?>

                    <h4><?php echo htmlspecialchars($hab["nombre"], ENT_QUOTES, "UTF-8"); ?></h4>
                    <p class="precio">$<?php echo number_format($hab["precio"], 2); ?> por noche</p>

                    <?php if ($hab["disponibles"] > 0) { ?>
                        <p>Disponibles: <?php echo (int)$hab["disponibles"]; ?></p>
                    <?php } else { ?>
                        <p class="texto-error">Agotada</p>
                    <?php } ?>

                    <div class="tarjeta-botones">
                        <button type="button" class="boton" onclick="verDetalles(<?php echo (int)$hab["id"]; ?>)">Ver detalles</button>

                        <?php if ($rol != "" && $hab["disponibles"] > 0) { ?>
                            <!-- Solo con sesion iniciada: el visitante no ve este boton -->
                            <button type="button" class="boton"
                                    onclick="agregarHabitacion(<?php echo (int)$hab["id"]; ?>, <?php echo (int)$hab["disponibles"]; ?>)">Agregar al carrito</button>
                        <?php } ?>
                    </div>
                </article>
            <?php } ?>
        </div>
    </section>
<?php } ?>

<!-- CU-09: ventana emergente. Esta oculta hasta que se pulsa "Ver detalles". -->
<div id="ventana" class="ventana-fondo" style="display: none;" onclick="cerrarVentana()">
    <div class="ventana-caja" role="dialog" aria-modal="true" aria-labelledby="ventana-nombre" onclick="event.stopPropagation()">
        <h3 id="ventana-nombre"></h3>
        <img id="ventana-imagen" src="" alt="">
        <p id="ventana-descripcion"></p>
        <p>Precio por noche: <strong id="ventana-precio"></strong></p>
        <p id="ventana-disponibles"></p>
        <button type="button" class="boton boton-cancelar" onclick="cerrarVentana()">Cerrar</button>
    </div>
</div>

<?php if ($rol != "") { ?>
    <!-- IMPORTANTE: primero validaciones.js y despues carrito.js, en este orden,
         porque carrito.js usa numeroValido() de validaciones.js. -->
    <script src="<?php echo $raiz; ?>js/validaciones.js"></script>
    <script src="<?php echo $raiz; ?>carrito/carrito.js"></script>
<?php } ?>
<script src="catalogo.js"></script>

<?php include($raiz . "includes/pie.php"); ?>