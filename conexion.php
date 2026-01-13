<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "vinyl_lab";

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);
$conn->set_charset("utf8mb4");
?>