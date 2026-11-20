<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["rol"])) {
    header("Location: ../index.php");
    exit;
}

$titulo = "Iniciar sesión";
$raiz = "../";
$mensaje = "";
$usuario = "";

require_once($raiz . "includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";

    if ($usuario === "" || $contrasena === "") {
        $mensaje = "Escribe tu usuario y contraseña.";
    } else {
        $consulta = mysqli_prepare(
            $conexion,
            "SELECT usuario_id, nombre, contrasena, rol FROM usuarios WHERE usuario = ?"
        );
        mysqli_stmt_bind_param($consulta, "s", $usuario);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_bind_result($consulta, $idUsuario, $nombreUsuario, $hash, $rol);
        $encontrado = mysqli_stmt_fetch($consulta);
        mysqli_stmt_close($consulta);

        if ($encontrado && password_verify($contrasena, $hash)) {
            $_SESSION["usuario_id"] = $idUsuario;
            $_SESSION["usuario_nombre"] = $nombreUsuario;
            $_SESSION["rol"] = $rol;
            mysqli_close($conexion);
            header("Location: ../index.php");
            exit;
        }

        $mensaje = "El usuario o la contraseña son incorrectos.";
    }
}

mysqli_close($conexion);
include($raiz . "includes/encabezado.php");
?>

<h2>Iniciar sesión</h2>

<script src="../js/validaciones.js"></script>
<script>
function validarLogin() {
    var usuario = document.getElementById("usuario").value;
    var contrasena = document.getElementById("contrasena").value;

    if (campoVacio(usuario) || campoVacio(contrasena)) {
        alert("Escribe tu usuario y contraseña.");
        return false;
    }
    return true;
}
</script>

<?php if ($mensaje !== "") { ?>
    <p role="alert"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<form method="post" action="login.php" onsubmit="return validarLogin();">
    <p>
        <label for="usuario">Nombre de usuario:</label><br>
        <input type="text" id="usuario" name="usuario" maxlength="50" autocomplete="username" required
               value="<?php echo htmlspecialchars($usuario, ENT_QUOTES, "UTF-8"); ?>">
    </p>
    <p>
        <label for="contrasena">Contraseña:</label><br>
        <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>
    </p>
    <button type="submit">Iniciar sesión</button>
    <a href="../index.php">Cancelar</a>
</form>
<p>¿No tienes cuenta? <a href="registro.php">Regístrate</a>.</p>

<?php include($raiz . "includes/pie.php"); ?>
