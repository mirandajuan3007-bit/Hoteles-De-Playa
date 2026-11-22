<?php
// CU-01 Registrar huesped.
// Un visitante crea su cuenta. Siempre se registra con rol "huesped";
// el administrador ya viene cargado en playa.sql.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya inicio sesion, no tiene caso registrarse.
if (isset($_SESSION["rol"])) {
    header("Location: ../index.php");
    exit;
}

$error = "";
$nombre = "";
$correo = "";
$usuario = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $usuario = trim($_POST["usuario"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";

    // Se valida otra vez en PHP por si el navegador tiene JavaScript apagado.
    if ($nombre == "" || $correo == "" || $usuario == "" || $contrasena == "") {
        $error = "Completa todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Escribe un correo válido.";
    } elseif ($contrasena != $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } else {
        include_once("../includes/conexion.php");

        // Revisa que el correo o el usuario no esten ocupados.
        $consulta = mysqli_prepare($conexion, "SELECT usuario_id FROM usuarios WHERE correo = ? OR usuario = ?");
        mysqli_stmt_bind_param($consulta, "ss", $correo, $usuario);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_store_result($consulta);
        $yaExiste = mysqli_stmt_num_rows($consulta) > 0;
        mysqli_stmt_close($consulta);

        if ($yaExiste) {
            $error = "Ese correo o nombre de usuario ya está registrado.";
        } else {
            // La contraseña se guarda cifrada.
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $rol = "huesped";
            $consulta = mysqli_prepare($conexion, "INSERT INTO usuarios (nombre, correo, usuario, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($consulta, "sssss", $nombre, $correo, $usuario, $hash, $rol);
            $guardado = mysqli_stmt_execute($consulta);
            mysqli_stmt_close($consulta);

            if ($guardado) {
                mysqli_close($conexion);
                // Lleva al inicio de sesion, que muestra el mensaje de exito.
                header("Location: login.php?registro=1");
                exit;
            }
            $error = "No se pudo crear la cuenta. Inténtalo de nuevo.";
        }
        mysqli_close($conexion);
    }
}

$titulo = "Registrarse";
$raiz = "../";
include($raiz . "includes/encabezado.php");
?>

<h2>Crear cuenta de huésped</h2>

<!-- novalidate: la validacion la hace JavaScript (validarRegistro) y no el navegador. -->
<form class="formulario" method="post" action="registro.php" onsubmit="return validarRegistro();" novalidate>
    <div id="mensaje-error" class="mensaje-error" role="alert" <?php if ($error == "") { echo 'style="display: none;"'; } ?>>
        <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
    </div>

    <label for="nombre">Nombre completo</label>
    <input type="text" id="nombre" name="nombre" maxlength="100" required
           value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, "UTF-8"); ?>">

    <label for="correo">Correo</label>
    <input type="email" id="correo" name="correo" maxlength="100" required
           value="<?php echo htmlspecialchars($correo, ENT_QUOTES, "UTF-8"); ?>">

    <label for="usuario">Nombre de usuario</label>
    <input type="text" id="usuario" name="usuario" maxlength="50" autocomplete="username" required
           value="<?php echo htmlspecialchars($usuario, ENT_QUOTES, "UTF-8"); ?>">

    <label for="contrasena">Contraseña</label>
    <input type="password" id="contrasena" name="contrasena" autocomplete="new-password" required>

    <label for="confirmar">Confirmar contraseña</label>
    <input type="password" id="confirmar" name="confirmar" autocomplete="new-password" required>

    <div class="botones">
        <button type="submit" class="boton">Registrarse</button>
        <a href="<?php echo $raiz; ?>index.php" class="boton boton-cancelar">Cancelar</a>
    </div>

    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
</form>

<script src="<?php echo $raiz; ?>js/validaciones.js"></script>
<script>
// Muestra un error arriba del formulario y pone el cursor en el campo.
function mostrarError(texto, campo) {
    var mensaje = document.getElementById("mensaje-error");
    mensaje.innerHTML = texto;
    mensaje.style.display = "block";
    document.getElementById(campo).focus();
    return false;
}

// Revisa los campos y pide confirmar antes de crear la cuenta.
function validarRegistro() {
    var nombre = document.getElementById("nombre").value.trim();
    var correo = document.getElementById("correo").value.trim();
    var usuario = document.getElementById("usuario").value.trim();
    var contrasena = document.getElementById("contrasena").value;
    var confirmar = document.getElementById("confirmar").value;

    if (campoVacio(nombre)) {
        return mostrarError("Escribe tu nombre.", "nombre");
    }
    if (campoVacio(correo)) {
        return mostrarError("Escribe tu correo.", "correo");
    }
    if (!correoValido(correo)) {
        return mostrarError("Escribe un correo válido.", "correo");
    }
    if (campoVacio(usuario)) {
        return mostrarError("Escribe un nombre de usuario.", "usuario");
    }
    if (campoVacio(contrasena)) {
        return mostrarError("Escribe una contraseña.", "contrasena");
    }
    if (contrasena != confirmar) {
        return mostrarError("Las contraseñas no coinciden.", "confirmar");
    }
    return confirmarAccion("¿Deseas crear tu cuenta?");
}
</script>

<?php include($raiz . "includes/pie.php"); ?>
