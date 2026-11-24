<?php
include_once("../includes/permisos.php");
permitirSolo(array("admin"));

$titulo = "Agregar habitación";
$raiz = "../";
$error = "";
$nombre = "";
$categoria = "";
$descripcion = "";
$precio = "";
$disponibles = "";

require_once($raiz . "includes/conexion.php");
require_once("funciones.php");
$categorias = obtenerCategoriasAdmin($conexion);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = trim($_POST["precio"] ?? "");
    $disponibles = trim($_POST["disponibles"] ?? "");
    $imagenes = prepararImagenesSubidas($_FILES["imagenes"] ?? array(), $error);

    if ($error === "" && !validarDatosHabitacion($conexion, $nombre, $categoria, $descripcion, $precio, $disponibles, $error)) {
        // El mensaje se define en validarDatosHabitacion().
    } elseif ($error === "" && count($imagenes) === 0) {
        $error = "Selecciona al menos una imagen para la habitación.";
    }

    if ($error === "") {
        $consulta = mysqli_prepare(
            $conexion,
            "INSERT INTO habitaciones (nombre, categoria, descripcion, precio, disponibles) VALUES (?, ?, ?, ?, ?)"
        );
        $precioNumero = (float)$precio;
        $disponiblesNumero = (int)$disponibles;
        mysqli_stmt_bind_param($consulta, "sssdi", $nombre, $categoria, $descripcion, $precioNumero, $disponiblesNumero);

        if (mysqli_stmt_execute($consulta)) {
            $idHabitacion = mysqli_insert_id($conexion);
            mysqli_stmt_close($consulta);

            if (guardarImagenesHabitacion($conexion, $idHabitacion, $imagenes, $error)) {
                mysqli_close($conexion);
                header("Location: index.php?agregada=1");
                exit;
            }

            $borrar = mysqli_prepare($conexion, "DELETE FROM habitaciones WHERE id = ?");
            mysqli_stmt_bind_param($borrar, "i", $idHabitacion);
            mysqli_stmt_execute($borrar);
            mysqli_stmt_close($borrar);
        } else {
            $error = "No se pudo agregar la habitación.";
            mysqli_stmt_close($consulta);
        }
    }
}

mysqli_close($conexion);
include($raiz . "includes/encabezado.php");
?>

<link rel="stylesheet" href="admin.css">
<h2>Agregar habitación</h2>

<?php if ($error !== "") { ?>
    <div class="mensaje-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></div>
<?php } ?>

<form class="formulario" method="post" action="agregar.php" enctype="multipart/form-data" onsubmit="return validarDatosHabitacion();">
    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" maxlength="100" required
           value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, "UTF-8"); ?>">

    <label for="categoria">Categoría</label>
    <select id="categoria" name="categoria" required>
        <option value="">Selecciona una categoría</option>
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

    <label for="imagenes">Imágenes (JPG, PNG o WebP; máximo 5 MB cada una)</label>
    <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg,image/png,image/webp" multiple required>

    <div class="botones">
        <button type="submit" class="boton">Guardar habitación</button>
        <a href="index.php" class="boton boton-cancelar">Cancelar</a>
    </div>
</form>

<script src="<?php echo $raiz; ?>js/validaciones.js"></script>
<script src="admin.js"></script>
<?php include($raiz . "includes/pie.php"); ?>
