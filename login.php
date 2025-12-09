<?php
session_start();
require 'conexion.php';

$nombre = $_POST['nombre'] ?? '';
$pass = $_POST['pass'] ?? '';

// Sanitizar entradas
$nombre = $conn->real_escape_string($nombre);
$pass = $conn->real_escape_string($pass);

// Consulta
$sql = "SELECT * FROM usuarios WHERE nombre='$nombre' AND pass='$pass'";
$resultado = $conn->query($sql);

if ($resultado && $resultado->num_rows === 1) {
    $_SESSION['usuario'] = $nombre;
    header("Location: index.html");
    exit();
} else {
    echo "<script>alert('Nombre de usuario o contraseña incorrectos'); window.location.href='login.html';</script>";
}

$conn->close();
?>
