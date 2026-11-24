<?php
// CU-10 Buscar habitaciones.
// La puede usar cualquier usuario (visitante, huesped o administrador),
// por eso NO incluye permisos.php.
$titulo = "Buscar habitaciones";
$raiz = "../";

require_once($raiz . "includes/conexion.php");

$texto = "";
$categoria = "";
$precioMin = "";
$precioMax = "";
$error = "";
$buscado = false;
$resultados = array();
$categorias = array();

// El formulario usa GET, asi que la busqueda se puede copiar como enlace.
if (isset($_GET["buscar"])) {
    $buscado = true;

    if (isset($_GET["texto"]) && is_string($_GET["texto"])) {
        $texto = trim($_GET["texto"]);
    }
    if (isset($_GET["categoria"]) && is_string($_GET["categoria"])) {
        $categoria = trim($_GET["categoria"]);
    }
    if (isset($_GET["precio_min"]) && is_string($_GET["precio_min"])) {
        $precioMin = trim($_GET["precio_min"]);
    }
    if (isset($_GET["precio_max"]) && is_string($_GET["precio_max"])) {
        $precioMax = trim($_GET["precio_max"]);
    }

    // Se valida tambien en PHP por si el navegador tiene JavaScript apagado.
    if (strlen($texto) > 100) {
        $error = "El texto de búsqueda no puede pasar de 100 caracteres.";
    } elseif ($precioMin != "" && (!preg_match('/^\d+(\.\d{1,2})?$/', $precioMin) || (float)$precioMin > 1000000)) {
        $error = "El precio mínimo debe ser un número de hasta dos decimales entre 0 y 1000000.";
    } elseif ($precioMax != "" && (!preg_match('/^\d+(\.\d{1,2})?$/', $precioMax) || (float)$precioMax > 1000000)) {
        $error = "El precio máximo debe ser un número de hasta dos decimales entre 0 y 1000000.";
    } elseif ($precioMin != "" && $precioMax != "" && (float)$precioMin > (float)$precioMax) {
        $error = "El precio mínimo no puede ser mayor que el precio máximo.";
    }
}

try {
    // Categorias para el campo de seleccion.
    $resultadoCategorias = mysqli_query($conexion, "SELECT nombre FROM categorias ORDER BY nombre");
    while ($fila = mysqli_fetch_assoc($resultadoCategorias)) {
        $categorias[] = $fila["nombre"];
    }

    if ($buscado && $error == "") {
        // Los criterios que se dejan vacios no filtran nada:
        //   texto vacio      -> "%%" (coincide con todo)
        //   categoria vacia  -> la condicion (? = '') se cumple
        //   precio minimo    -> 0 si esta vacio
        //   precio maximo    -> un numero muy grande si esta vacio
        $textoLike = "%" . $texto . "%";
        $minimo = 0;
        if ($precioMin != "") {
            $minimo = (float)$precioMin;
        }
        $maximo = 99999999;
        if ($precioMax != "") {
            $maximo = (float)$precioMax;
        }

        // Consulta preparada: lo que escribe la persona nunca se pega directo en el SQL.
        $sql = "SELECT id, nombre, categoria, descripcion, precio, disponibles
                FROM habitaciones
                WHERE (nombre LIKE ? OR descripcion LIKE ?)
                  AND (? = '' OR categoria = ?)
                  AND precio >= ? AND precio <= ?
                ORDER BY categoria, nombre";
        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "ssssdd", $textoLike, $textoLike, $categoria, $categoria, $minimo, $maximo);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_bind_result($consulta, $idHab, $nombreHab, $categoriaHab, $descripcionHab, $precioHab, $disponiblesHab);
        while (mysqli_stmt_fetch($consulta)) {
            $resultados[] = array(
                "id" => $idHab,
                "nombre" => $nombreHab,
                "categoria" => $categoriaHab,
                "descripcion" => $descripcionHab,
                "precio" => $precioHab,
                "disponibles" => $disponiblesHab
            );
        }
        mysqli_stmt_close($consulta);
    }
} catch (Throwable $e) {
    // Si algo falla con la base de datos, se muestra aqui en vez de dejar la pagina en blanco.
    $error = "Error al consultar la base de datos: " . $e->getMessage();
}

mysqli_close($conexion);
include($raiz . "includes/encabezado.php");
?>

<link rel="stylesheet" href="busqueda.css">

<h2>Buscar habitaciones</h2>

<?php if ($error != "") { ?>
    <div class="mensaje-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></div>
<?php } ?>

<form class="formulario formulario-busqueda" method="get" action="index.php" onsubmit="return validarBusqueda();">
    <input type="hidden" name="buscar" value="1">

    <label for="texto">Texto (nombre o descripción)</label>
    <input type="text" id="texto" name="texto" maxlength="100"
           value="<?php echo htmlspecialchars($texto, ENT_QUOTES, "UTF-8"); ?>">

    <label for="categoria">Categoría</label>
    <select id="categoria" name="categoria">
        <option value="">Todas</option>
        <?php foreach ($categorias as $nombreCategoria) { ?>
            <option value="<?php echo htmlspecialchars($nombreCategoria, ENT_QUOTES, "UTF-8"); ?>"
                <?php if ($nombreCategoria == $categoria) { echo "selected"; } ?>>
                <?php echo htmlspecialchars($nombreCategoria, ENT_QUOTES, "UTF-8"); ?>
            </option>
        <?php } ?>
    </select>

    <label for="precio_min">Precio mínimo por noche</label>
    <input type="number" id="precio_min" name="precio_min" min="0" max="1000000" step="0.01" inputmode="decimal"
           value="<?php echo htmlspecialchars($precioMin, ENT_QUOTES, "UTF-8"); ?>">

    <label for="precio_max">Precio máximo por noche</label>
    <input type="number" id="precio_max" name="precio_max" min="0" max="1000000" step="0.01" inputmode="decimal"
           value="<?php echo htmlspecialchars($precioMax, ENT_QUOTES, "UTF-8"); ?>">

    <div class="botones">
        <button type="submit" class="boton">Buscar</button>
        <a href="<?php echo $raiz; ?>index.php" class="boton boton-cancelar">Cancelar</a>
    </div>
</form>

<?php if ($buscado && $error == "") { ?>
    <h3>Resultados</h3>

    <?php if (count($resultados) == 0) { ?>
        <p>No se encontraron habitaciones con esos criterios.</p>
    <?php } else { ?>
        <p>Se encontraron <?php echo count($resultados); ?> habitación(es).</p>

        <!-- Tabla tipo reporte con los datos de cada habitacion -->
        <table class="tabla">
            <thead>
                <tr>
                    <th>Habitación</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Precio por noche</th>
                    <th>Disponibles</th>
                    <th>Catálogo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $hab) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hab["nombre"], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($hab["categoria"], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($hab["descripcion"], ENT_QUOTES, "UTF-8"); ?></td>
                        <td>$<?php echo number_format($hab["precio"], 2); ?></td>
                        <td>
                            <?php if ($hab["disponibles"] > 0) { ?>
                                <?php echo (int)$hab["disponibles"]; ?>
                            <?php } else { ?>
                                <span class="texto-error">Agotada</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="<?php echo $raiz; ?>catalogo/index.php?categoria=<?php echo urlencode($hab["categoria"]); ?>">Ver en catálogo</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
<?php } ?>

<!-- Primero validaciones.js (funciones comunes de Jorge) y despues el JavaScript de esta pagina -->
<script src="<?php echo $raiz; ?>js/validaciones.js"></script>
<script src="busqueda.js"></script>

<?php include($raiz . "includes/pie.php"); ?>
