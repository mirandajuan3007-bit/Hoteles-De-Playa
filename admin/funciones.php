<?php
include_once("../includes/permisos.php");
permitirSolo(array("admin"));

function obtenerCategoriasAdmin($conexion) {
    $categorias = array();
    $resultado = mysqli_query($conexion, "SELECT nombre FROM categorias ORDER BY nombre");
    if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $categorias[] = $fila["nombre"];
        }
    }
    return $categorias;
}

function validarDatosHabitacion($conexion, $nombre, $categoria, $descripcion, $precio, $disponibles, &$error) {
    if ($nombre === "" || $categoria === "" || $descripcion === "" || $precio === "" || $disponibles === "") {
        $error = "Completa todos los campos.";
        return false;
    }

    if (!preg_match('/^\d+(\.\d{1,2})?$/', $precio) || (float)$precio > 1000000) {
        $error = "El precio debe ser un número entre 0 y 1000000, con hasta dos decimales.";
        return false;
    }

    if (!ctype_digit($disponibles) || (int)$disponibles > 100000) {
        $error = "La disponibilidad debe ser un número entero entre 0 y 100000.";
        return false;
    }

    $consulta = mysqli_prepare($conexion, "SELECT nombre FROM categorias WHERE nombre = ?");
    mysqli_stmt_bind_param($consulta, "s", $categoria);
    mysqli_stmt_execute($consulta);
    mysqli_stmt_store_result($consulta);
    $categoriaExiste = mysqli_stmt_num_rows($consulta) > 0;
    mysqli_stmt_close($consulta);

    if (!$categoriaExiste) {
        $error = "Selecciona una categoría válida.";
        return false;
    }

    return true;
}

function prepararImagenesSubidas($archivos, &$error) {
    $imagenes = array();

    if (!isset($archivos["name"]) || !is_array($archivos["name"])) {
        return $imagenes;
    }

    $extensiones = array(
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    );

    foreach ($archivos["name"] as $indice => $nombreOriginal) {
        $codigoError = $archivos["error"][$indice];
        if ($codigoError === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($codigoError !== UPLOAD_ERR_OK) {
            $error = "No se pudo recibir una de las imágenes.";
            return false;
        }

        $temporal = $archivos["tmp_name"][$indice];
        $tamano = (int)$archivos["size"][$indice];
        if ($tamano <= 0 || $tamano > 5 * 1024 * 1024) {
            $error = "Cada imagen debe pesar menos de 5 MB.";
            return false;
        }

        $informacion = @getimagesize($temporal);
        if ($informacion === false || !isset($extensiones[$informacion["mime"]])) {
            $error = "Usa imágenes JPG, PNG o WebP.";
            return false;
        }

        $imagenes[] = array(
            "temporal" => $temporal,
            "extension" => $extensiones[$informacion["mime"]]
        );
    }

    return $imagenes;
}

function guardarImagenesHabitacion($conexion, $idHabitacion, $imagenes, &$error) {
    if (count($imagenes) === 0) {
        return true;
    }

    $carpeta = __DIR__ . "/../imagenes/habitaciones";
    if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true)) {
        $error = "No se pudo preparar la carpeta de imágenes.";
        return false;
    }

    $consulta = mysqli_prepare($conexion, "INSERT INTO imagenes (id_habitacion, ruta) VALUES (?, ?)");
    if (!$consulta) {
        $error = "No se pudieron guardar las imágenes.";
        return false;
    }

    $guardadas = array();
    foreach ($imagenes as $imagen) {
        $nombreArchivo = uniqid("habitacion_", true) . "." . $imagen["extension"];
        $ruta = "imagenes/habitaciones/" . $nombreArchivo;
        $destino = $carpeta . "/" . $nombreArchivo;

        if (!move_uploaded_file($imagen["temporal"], $destino)) {
            $error = "No se pudo guardar una de las imágenes.";
            mysqli_stmt_close($consulta);
            borrarImagenesGuardadas($conexion, $guardadas);
            return false;
        }

        mysqli_stmt_bind_param($consulta, "is", $idHabitacion, $ruta);
        if (!mysqli_stmt_execute($consulta)) {
            unlink($destino);
            $error = "No se pudo guardar una de las imágenes.";
            mysqli_stmt_close($consulta);
            borrarImagenesGuardadas($conexion, $guardadas);
            return false;
        }

        $guardadas[] = $ruta;
    }

    mysqli_stmt_close($consulta);
    return true;
}

function borrarImagenesGuardadas($conexion, $rutas) {
    $consulta = mysqli_prepare($conexion, "DELETE FROM imagenes WHERE ruta = ?");
    foreach ($rutas as $ruta) {
        mysqli_stmt_bind_param($consulta, "s", $ruta);
        mysqli_stmt_execute($consulta);
        borrarArchivoHabitacion($ruta);
    }
    mysqli_stmt_close($consulta);
}

function obtenerImagenesHabitacion($conexion, $idHabitacion) {
    $imagenes = array();
    $consulta = mysqli_prepare($conexion, "SELECT id_imagen, ruta FROM imagenes WHERE id_habitacion = ? ORDER BY id_imagen");
    mysqli_stmt_bind_param($consulta, "i", $idHabitacion);
    mysqli_stmt_execute($consulta);
    mysqli_stmt_bind_result($consulta, $idImagen, $ruta);
    while (mysqli_stmt_fetch($consulta)) {
        $imagenes[] = array("id" => $idImagen, "ruta" => $ruta);
    }
    mysqli_stmt_close($consulta);
    return $imagenes;
}

function eliminarImagenHabitacion($conexion, $idHabitacion, $idImagen) {
    $consulta = mysqli_prepare($conexion, "SELECT ruta FROM imagenes WHERE id_imagen = ? AND id_habitacion = ?");
    mysqli_stmt_bind_param($consulta, "ii", $idImagen, $idHabitacion);
    mysqli_stmt_execute($consulta);
    mysqli_stmt_bind_result($consulta, $ruta);
    $encontrada = mysqli_stmt_fetch($consulta);
    mysqli_stmt_close($consulta);

    if (!$encontrada) {
        return false;
    }

    $consulta = mysqli_prepare($conexion, "DELETE FROM imagenes WHERE id_imagen = ? AND id_habitacion = ?");
    mysqli_stmt_bind_param($consulta, "ii", $idImagen, $idHabitacion);
    $eliminada = mysqli_stmt_execute($consulta);
    mysqli_stmt_close($consulta);

    if ($eliminada) {
        borrarArchivoHabitacion($ruta);
    }
    return $eliminada;
}

function borrarArchivoHabitacion($ruta) {
    $prefijo = "imagenes/habitaciones/";
    if (substr($ruta, 0, strlen($prefijo)) !== $prefijo) {
        return;
    }

    $nombreArchivo = substr($ruta, strlen($prefijo));
    if ($nombreArchivo === "" || basename($nombreArchivo) !== $nombreArchivo) {
        return;
    }

    $archivo = __DIR__ . "/../" . $ruta;
    if (is_file($archivo)) {
        unlink($archivo);
    }
}
?>
