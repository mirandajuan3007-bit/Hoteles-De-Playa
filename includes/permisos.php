<?php
// CU-04 Restringir acceso a paginas protegidas.
// Cada pagina protegida lo incluye al inicio, antes de mostrar HTML, e indica
// que roles pueden entrar:
//   include_once("../includes/permisos.php");
//   permitirSolo(array("admin"));             solo el administrador
//   permitirSolo(array("huesped", "admin"));  cualquier usuario con sesion
// Las paginas protegidas estan dentro de una carpeta (admin/, carrito/, pago/),
// por eso las rutas empiezan con "../".
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function permitirSolo($roles) {
    // Visitante: no ha iniciado sesion, se le manda a iniciar sesion.
    if (!isset($_SESSION["rol"])) {
        $_SESSION["mensaje"] = "Acceso no autorizado. Inicia sesión para continuar.";
        header("Location: ../autenticacion/login.php");
        exit;
    }

    // Tiene sesion, pero su rol no puede ver esta pagina.
    if (!in_array($_SESSION["rol"], $roles, true)) {
        $_SESSION["mensaje"] = "Acceso no autorizado.";
        header("Location: ../index.php");
        exit;
    }
}
