<?php
$host = "localhost";
$usuario = "root";         // Ajusta según tu config
$contrasena = "";          // Ajusta según tu config
$base_datos = "login_vinyl";
$puerto = 3307;            // ⚠️ Reemplaza con tu puerto real

$conn = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
