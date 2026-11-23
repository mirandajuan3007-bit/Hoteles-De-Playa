<?php
$titulo = "Inicio";
$raiz = "";
include($raiz . "includes/encabezado.php");
?>

<!-- Portada: foto grande con el texto delante -->
<section class="portada">
    <div class="portada-texto">
        <?php if ($rol == "") { ?>
            <!-- Visitante -->
            <span class="etiqueta">Todo incluido</span>
            <h2>Tu descanso frente al mar</h2>
            <p>Habitaciones cómodas, playa de arena blanca y atardeceres inolvidables.</p>
            <a href="<?php echo $raiz; ?>catalogo/index.php" class="boton">Ver habitaciones</a>
            <a href="<?php echo $raiz; ?>autenticacion/registro.php" class="boton boton-claro">Crear cuenta</a>
        <?php } else { ?>
            <!-- Usuario con sesion: saludo con su nombre y accesos segun su rol -->
            <span class="etiqueta">Bienvenido de nuevo</span>
            <h2>Hola, <?php echo htmlspecialchars($_SESSION["usuario_nombre"], ENT_QUOTES, "UTF-8"); ?></h2>
            <p>¿Listo para tu próxima escapada al mar? Elige tu habitación y reserva en pocos pasos.</p>
            <a href="<?php echo $raiz; ?>catalogo/index.php" class="boton">Ver habitaciones</a>
            <a href="<?php echo $raiz; ?>carrito/index.php" class="boton boton-claro">Mi carrito</a>
            <?php if ($rol == "admin") { ?>
                <a href="<?php echo $raiz; ?>admin/index.php" class="boton boton-claro">Administración</a>
            <?php } ?>
        <?php } ?>
    </div>
</section>

<!-- Bienvenida: texto y foto en dos columnas -->
<section class="bienvenida">
    <div class="bienvenida-texto">
        <h2>Bienvenido a Hoteles de Playa</h2>
        <p>Disfruta de unas vacaciones a la orilla del mar. Contamos con habitaciones para parejas,
           familias y grupos, todas a unos pasos de la playa.</p>
        <p>Consulta nuestras habitaciones, elige tus fechas y reserva tu estancia en pocos pasos.</p>
    </div>
    <img src="<?php echo $raiz; ?>imagenes/inicio/hotel-playa.jpg" alt="Alberca del hotel con vista al mar">
</section>

<!-- Servicios del hotel -->
<section class="servicios">
    <div class="servicio">
        <h3>Playa privada</h3>
        <p>Camastros y sombrillas en la arena para todos nuestros huéspedes.</p>
    </div>
    <div class="servicio">
        <h3>Alberca</h3>
        <p>Albercas para adultos y niños con vista al mar.</p>
    </div>
    <div class="servicio">
        <h3>Restaurante</h3>
        <p>Comida regional e internacional durante todo el día.</p>
    </div>
</section>

<!-- Invitacion final con la foto del atardecer de fondo -->
<section class="invitacion">
    <h2>Reserva tu estancia</h2>
    <p>Elige tu habitación y vive el mar como nunca.</p>
    <a href="<?php echo $raiz; ?>catalogo/index.php" class="boton">Ver habitaciones</a>
</section>

<?php include($raiz . "includes/pie.php"); ?>
