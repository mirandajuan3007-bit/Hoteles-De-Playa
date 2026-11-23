<?php
include_once("../includes/permisos.php");
permitirSolo(array("admin"));

$titulo = "Administración de habitaciones";
$raiz = "../";
require_once($raiz . "includes/conexion.php");

$resultado = mysqli_query(
    $conexion,
    "SELECT h.id, h.nombre, h.categoria, h.precio, h.disponibles,
            (SELECT i.ruta FROM imagenes i WHERE i.id_habitacion = h.id ORDER BY i.id_imagen LIMIT 1) AS imagen
     FROM habitaciones h
     ORDER BY h.categoria, h.nombre"
);

$habitaciones = array();
if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $habitaciones[] = $fila;
    }
}
mysqli_close($conexion);

include($raiz . "includes/encabezado.php");
?>

<link rel="stylesheet" href="admin.css">

<h2>Administrar habitaciones</h2>
<p><a href="agregar.php" class="boton">Agregar habitación</a></p>

<?php if (isset($_GET["agregada"])) { ?>
    <div class="mensaje-exito" role="status">La habitación se agregó correctamente.</div>
<?php } elseif (isset($_GET["editada"])) { ?>
    <div class="mensaje-exito" role="status">La habitación se actualizó correctamente.</div>
<?php } elseif (isset($_GET["eliminada"])) { ?>
    <div class="mensaje-exito" role="status">La habitación se eliminó correctamente.</div>
<?php } ?>

<?php if (count($habitaciones) === 0) { ?>
    <p>No hay habitaciones registradas.</p>
<?php } else { ?>
    <div class="admin-tabla-contenedor">
        <table class="tabla admin-tabla">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Habitación</th>
                    <th>Categoría</th>
                    <th>Precio por noche</th>
                    <th>Disponibles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($habitaciones as $habitacion) { ?>
                    <tr>
                        <td>
                            <?php if ($habitacion["imagen"] !== "" && file_exists(__DIR__ . "/../" . $habitacion["imagen"])) { ?>
                                <img src="<?php echo $raiz . htmlspecialchars($habitacion["imagen"], ENT_QUOTES, "UTF-8"); ?>"
                                     alt="<?php echo htmlspecialchars($habitacion["nombre"], ENT_QUOTES, "UTF-8"); ?>">
                            <?php } else { ?>
                                Sin imagen
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($habitacion["nombre"], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($habitacion["categoria"], ENT_QUOTES, "UTF-8"); ?></td>
                        <td>$<?php echo number_format($habitacion["precio"], 2); ?></td>
                        <td><?php echo (int)$habitacion["disponibles"]; ?></td>
                        <td>
                            <div class="admin-acciones">
                                <a href="editar.php?id=<?php echo (int)$habitacion["id"]; ?>" class="boton">Editar</a>
                                <a href="eliminar.php?id=<?php echo (int)$habitacion["id"]; ?>" class="boton boton-cancelar">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>

<script src="<?php echo $raiz; ?>js/validaciones.js"></script>
<script src="admin.js"></script>
<?php include($raiz . "includes/pie.php"); ?>
