<?php
$host = getenv('MYSQLHOST');
$usuario = getenv('MYSQLUSER');
$contrasena = getenv('MYSQLPASSWORD');
$base_datos = getenv('MYSQLDATABASE');


$conn = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);


if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
