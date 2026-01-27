<?php
$host        = getenv('MYSQLHOST') ?: 'localhost';
$usuario     = getenv('MYSQLUSER') ?: 'root';
$contrasena  = getenv('MYSQLPASSWORD') ?: '';
$base_datos  = getenv('MYSQLDATABASE') ?: 'tienda';
$puerto      = (int) (getenv('MYSQLPORT') ?: 3306);

$conn = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
