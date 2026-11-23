<?php
// CU-02 Iniciar sesion.
// El usuario escribe su usuario y contraseña; si son correctos se guardan sus
// datos en la sesion y el menu cambia segun su rol (admin o huesped).

// La sesion se abre aqui (y no en el encabezado) porque, si el login es
// correcto, hay que redirigir antes de mostrar cualquier HTML.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya inicio sesion, no tiene caso mostrar el formulario.
if (isset($_SESSION["rol"])) {
    header("Location: ../index.php");
    exit;
}

// No usar $usuario ni $contrasena: config.inc.php ya usa esos nombres
// ($GLOBALS["usuario"], $GLOBALS["contrasena"]) y se sobrescribirian.
$nombreUsuario = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreUsuario = trim($_POST["usuario"] ?? "");
    $clave = $_POST["contrasena"] ?? "";

    // Se valida otra vez en PHP por si el navegador tiene JavaScript apagado.
    if ($nombreUsuario == "" || $clave == "") {
        $error = "Escribe tu usuario y tu contraseña.";
    } else {
        include_once("../includes/conexion.php");

        // Consulta preparada: el usuario nunca se pega directo en el SQL.
        $sql = "SELECT usuario_id, nombre, contrasena, rol FROM usuarios WHERE usuario = ?";
        $consulta = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($consulta, "s", $nombreUsuario);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_bind_result($consulta, $id, $nombre, $hash, $rol);
        $encontrado = mysqli_stmt_fetch($consulta);
        mysqli_stmt_close($consulta);
        mysqli_close($conexion);

        // password_verify compara la contraseña escrita con la cifrada en la BD.
        if ($encontrado && password_verify($clave, $hash)) {
            // Se cambia el id de la sesion al entrar, por seguridad.
            session_regenerate_id(true);

            // Datos acordados con los demas modulos.
            $_SESSION["usuario_id"] = $id;
            $_SESSION["usuario_nombre"] = $nombre;
            $_SESSION["rol"] = $rol;

            header("Location: ../index.php");
            exit;
        }

        // El mismo mensaje para usuario inexistente o contraseña mal,
        // asi no se revela que usuarios existen.
        $error = "Usuario o contraseña incorrectos.";
    }
}

$titulo = "Iniciar sesión";
$raiz = "../";
include($raiz . "includes/encabezado.php");
?>

<!-- Diseño partido: formulario a la izquierda y foto a la derecha -->
<div class="acceso">
<div class="acceso-formulario">

<h2>Iniciar sesión</h2>

<?php if (isset($_GET["registro"]) && $error == "") { ?>
    <div class="mensaje-exito" role="status">Tu cuenta se creó correctamente. Ya puedes iniciar sesión.</div>
<?php } ?>

<!-- novalidate: la validacion la hace JavaScript (validarLogin) y no el navegador. -->
<form class="formulario" method="post" action="login.php" onsubmit="return validarLogin();" novalidate>
    <div id="mensaje-error" class="mensaje-error" role="alert" <?php if ($error == "") { echo 'style="display: none;"'; } ?>>
        <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
    </div>

    <label for="usuario">Usuario</label>
    <input type="text" id="usuario" name="usuario" maxlength="50" autocomplete="username" required
           value="<?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, "UTF-8"); ?>">

    <label for="contrasena">Contraseña</label>
    <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>

    <div class="botones">
        <button type="submit" class="boton">Iniciar sesión</button>
        <a href="<?php echo $raiz; ?>index.php" class="boton boton-cancelar">Cancelar</a>
    </div>

    <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
</form>

</div>

<div class="acceso-foto" style="background-image: url('<?php echo $raiz; ?>imagenes/inicio/playa-uno.webp');">
    <p class="acceso-frase">Tu lugar frente al mar te espera</p>
    <p>Inicia sesión para reservar tu estancia.</p>
</div>
</div>

<script src="<?php echo $raiz; ?>js/validaciones.js"></script>
<script>
// Revisa que los dos campos esten llenos antes de enviar el formulario.
function validarLogin() {
    var usuario = document.getElementById("usuario").value.trim();
    var contrasena = document.getElementById("contrasena").value;
    var mensaje = document.getElementById("mensaje-error");

    if (campoVacio(usuario)) {
        mensaje.innerHTML = "Escribe tu usuario.";
        mensaje.style.display = "block";
        document.getElementById("usuario").focus();
        return false;
    }
    if (campoVacio(contrasena)) {
        mensaje.innerHTML = "Escribe tu contraseña.";
        mensaje.style.display = "block";
        document.getElementById("contrasena").focus();
        return false;
    }
    return true;
}
</script>

<?php include($raiz . "includes/pie.php"); ?>
