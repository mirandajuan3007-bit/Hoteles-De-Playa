<?php
include_once("../includes/permisos.php");
permitirSolo(array("admin"));

$titulo = "Editar habitación";
$raiz = "../";
$error = "";
$idHabitacion = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($idHabitacion <= 0) {
    header("Location: index.php");
    exit;
}

require_once($raiz . "includes/conexion.php");
require_once("funciones.php");
$categorias = obtenerCategoriasAdmin($conexion);

$consulta = mysqli_prepare($conexion, "SELECT nombre, categoria, descripcion, precio, disponibles FROM habitaciones WHERE id = ?");
mysqli_stmt_bind_param($consulta, "i", $idHabitacion);
mysqli_stmt_execute($consulta);
mysqli_stmt_bind_result($consulta, $nombre, $categoria, $descripcion, $precio, $disponibles);
$encontrada = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);

if (!$encontrada) {
    mysqli_close($conexion);
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = trim($_POST["precio"] ?? "");
    $disponibles = trim($_POST["disponibles"] ?? "");
    $imagenesNuevas = prepararImagenesSubidas($_FILES["imagenes"] ?? array(), $error);

    if ($error === "" && !validarDatosHabitacion($conexion, $nombre, $categoria, $descripcion, $precio, $disponibles, $error)) {
        // El mensaje se define en validarDatosHabitacion().
    }

    $imagenesActuales = obtenerImagenesHabitacion($conexion, $idHabitacion);
    $quitar = array();
    if (isset($_POST["quitar_imagenes"]) && is_array($_POST["quitar_imagenes"])) {
        foreach ($_POST["quitar_imagenes"] as $idImagen) {
            if (ctype_digit((string)$idImagen)) {
                $quitar[(int)$idImagen] = true;
            }
        }
    }

    $imagenesQueQuedan = is_array($imagenesNuevas) ? count($imagenesNuevas) : 0;
    foreach ($imagenesActuales as $imagenActual) {
        if (!isset($quitar[(int)$imagenActual["id"]])) {
            $imagenesQueQuedan++;
        }
    }
    if ($error === "" && $imagenesQueQuedan === 0) {
        $error = "La habitación debe conservar o tener al menos una imagen.";
    }

    if ($error === "") {
        $consulta = mysqli_prepare(
            $conexion,
            "UPDATE habitaciones SET nombre = ?, categoria = ?, descripcion = ?, precio = ?, disponibles = ? WHERE id = ?"
        );
        $precioNumero = (float)$precio;
        $disponiblesNumero = (int)$disponibles;
        mysqli_stmt_bind_param($consulta, "sssdii", $nombre, $categoria, $descripcion, $precioNumero, $disponiblesNumero, $idHabitacion);
        $actualizada = mysqli_stmt_execute($consulta);
        mysqli_stmt_close($consulta);

        if ($actualizada && guardarImagenesHabitacion($conexion, $idHabitacion, $imagenesNuevas, $error)) {
            foreach ($imagenesActuales as $imagenActual) {
                if (isset($quitar[(int)$imagenActual["id"]])) {
                    eliminarImagenHabitacion($conexion, $idHabitacion, (int)$imagenActual["id"]);
                }
            }
            mysqli_close($conexion);
            header("Location: index.php?editada=1");
            exit;
        }

        if ($error === "") {
            $error = "No se pudieron actualizar los datos de la habitación.";
        }
    }
}

$imagenesActuales = obtenerImagenesHabitacion($conexion, $idHabitacion);
mysqli_close($conexion);
include($raiz . "includes/encabezado.php");
?>

<link rel="stylesheet" href="admin.css">
<h2>Editar habitación</h2>

<?php if ($error !== "") { ?>
    <div class="mensaje-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></div>
<?php } ?>

<form class="formulario" method="post" action="editar.php?id=<?php echo $idHabitacion; ?>" enctype="multipart/form-data" onsubmit="return validarDatosHabitacion();">
    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" maxlength="100" required
           value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, "UTF-8"); ?>">

    <label for="categoria">Categoría</label>
    <select id="categoria" name="categoria" required>
        <?php foreach ($categorias as $opcion) { ?>
            <option value="<?php echo htmlspecialchars($opcion, ENT_QUOTES, "UTF-8"); ?>"
                <?php if ($categoria === $opcion) { echo "selected"; } ?>>
                <?php echo htmlspecialchars($opcion, ENT_QUOTES, "UTF-8"); ?>
            </option>
        <?php } ?>
    </select>

    <label for="descripcion">Descripción</label>
    <textarea id="descripcion" name="descripcion" maxlength="255" required><?php echo htmlspecialchars($descripcion, ENT_QUOTES, "UTF-8"); ?></textarea>

    <label for="precio">Precio por noche</label>
    <input type="number" id="precio" name="precio" min="0" max="1000000" step="0.01" inputmode="decimal" required
           value="<?php echo htmlspecialchars($precio, ENT_QUOTES, "UTF-8"); ?>">

    <label for="disponibles">Habitaciones disponibles</label>
    <input type="number" id="disponibles" name="disponibles" min="0" max="100000" step="1" required
           value="<?php echo htmlspecialchars($disponibles, ENT_QUOTES, "UTF-8"); ?>">

    <h3>Imágenes actuales</h3>
    <div class="admin-imagenes">
        <?php foreach ($imagenesActuales as $imagen) { ?>
            <div class="admin-imagen">
                <?php if (file_exists(__DIR__ . "/../" . $imagen["ruta"])) { ?>
                    <img src="<?php echo $raiz . htmlspecialchars($imagen["ruta"], ENT_QUOTES, "UTF-8"); ?>" alt="Imagen de la habitación">
                <?php } else { ?>
                    <p>Archivo de imagen no encontrado.</p>
                <?php } ?>
                <label>
                    <input type="checkbox" name="quitar_imagenes[]" value="<?php echo (int)$imagen["id"]; ?>">
                    Quitar esta imagen
                </label>
            </div>
        <?php } ?>
    </div>

    <label for="imagenes">Agregar imágenes (JPG, PNG o WebP; máximo 5 MB cada una)</label>
    <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg,image/png,image/webp" multiple>

    <div class="botones">
        <button type="submit" class="boton">Guardar cambios</button>
        <a href="index.php" class="boton boton-cancelar">Cancelar</a>
    </div>
</form>

<script src="<?php echo $raiz; ?>js/validaciones.js"></script>
<script src="admin.js"></script>
<?php include($raiz . "includes/pie.php"); ?>
