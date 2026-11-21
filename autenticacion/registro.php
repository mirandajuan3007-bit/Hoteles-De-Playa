<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["rol"])) {
    header("Location: ../index.php");
    exit;
}

$titulo = "Registrarse";
$raiz = "../";
$mensaje = "";
$registroExitoso = false;
$nombre = "";
$correo = "";
$usuario = "";

require_once($raiz . "includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $usuario = trim($_POST["usuario"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";

    if ($nombre === "" || $correo === "" || $usuario === "" || $contrasena === "") {
        $mensaje = "Completa todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Escribe un correo válido.";
    } elseif ($contrasena !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        $consulta = mysqli_prepare(
            $conexion,
            "SELECT usuario_id FROM usuarios WHERE correo = ? OR usuario = ?"
        );
        mysqli_stmt_bind_param($consulta, "ss", $correo, $usuario);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_store_result($consulta);
        $yaExiste = mysqli_stmt_num_rows($consulta) > 0;
        mysqli_stmt_close($consulta);

        if ($yaExiste) {
            $mensaje = "Ese correo o nombre de usuario ya está registrado.";
        } else {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $rol = "huesped";
            $consulta = mysqli_prepare(
                $conexion,
                "INSERT INTO usuarios (nombre, correo, usuario, contrasena, rol) VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($consulta, "sssss", $nombre, $correo, $usuario, $hash, $rol);

            if (mysqli_stmt_execute($consulta)) {
                $registroExitoso = true;
                $mensaje = "Tu cuenta se creó correctamente. Ya puedes iniciar sesión.";
                $nombre = "";
                $correo = "";
                $usuario = "";
            } else {
                $mensaje = "No se pudo crear la cuenta. Inténtalo de nuevo.";
            }

            mysqli_stmt_close($consulta);
        }
    }
}

mysqli_close($conexion);
include($raiz . "includes/encabezado.php");
?>

<h2>Crear cuenta de huésped</h2>

<script src="../js/validaciones.js"></script>
<script>
function validarRegistro() {
    var nombre = document.getElementById("nombre").value;
    var correo = document.getElementById("correo").value;
    var usuario = document.getElementById("usuario").value;
    var contrasena = document.getElementById("contrasena").value;
    var confirmar = document.getElementById("confirmar").value;

    if (campoVacio(nombre) || campoVacio(correo) || campoVacio(usuario) ||
        campoVacio(contrasena) || campoVacio(confirmar)) {
        alert("Completa todos los campos.");
        return false;
    }
    if (!correoValido(correo)) {
        alert("Escribe un correo válido.");
        return false;
    }
    if (contrasena !== confirmar) {
        alert("Las contraseñas no coinciden.");
        return false;
    }
    return true;
}
</script>

<?php if ($mensaje !== "") { ?>
    <p role="status"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<?php if ($registroExitoso) { ?>
    <p><a href="login.php">Ir a iniciar sesión</a></p>
<?php } else { ?>
    <form method="post" action="registro.php" onsubmit="return validarRegistro();">
        <p>
            <label for="nombre">Nombre:</label><br>
            <input type="text" id="nombre" name="nombre" maxlength="100" required
                   value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, "UTF-8"); ?>">
        </p>
        <p>
            <label for="correo">Correo:</label><br>
            <input type="email" id="correo" name="correo" maxlength="100" required
                   value="<?php echo htmlspecialchars($correo, ENT_QUOTES, "UTF-8"); ?>">
        </p>
        <p>
            <label for="usuario">Nombre de usuario:</label><br>
            <input type="text" id="usuario" name="usuario" maxlength="50" required
                   value="<?php echo htmlspecialchars($usuario, ENT_QUOTES, "UTF-8"); ?>">
        </p>
        <p>
            <label for="contrasena">Contraseña:</label><br>
            <input type="password" id="contrasena" name="contrasena" autocomplete="new-password" required>
        </p>
        <p>
            <label for="confirmar">Confirmar contraseña:</label><br>
            <input type="password" id="confirmar" name="confirmar" autocomplete="new-password" required>
        </p>
        <button type="submit">Registrarse</button>
        <a href="../index.php">Cancelar</a>
    </form>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>.</p>
<?php } ?>

<?php include($raiz . "includes/pie.php"); ?>
