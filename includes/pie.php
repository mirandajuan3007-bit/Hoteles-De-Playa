<?php
// CU-04: si alguien abre este archivo directo en el navegador, lo manda al inicio.
if (!isset($raiz)) {
    header("Location: ../index.php");
    exit;
}
?>
</main>

<footer class="pie">
    <p>Hoteles de Playa</p>
</footer>

</body>
</html>
