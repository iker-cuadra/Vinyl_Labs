<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $pass = $_POST['pass'] ?? '';

    $nombre = $conn->real_escape_string($nombre);
    $pass = $conn->real_escape_string($pass);

    $sql = "SELECT * FROM usuarios WHERE nombre='$nombre' AND pass='$pass'";
    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows === 1) {
        $_SESSION['usuario'] = $nombre;
        $conn->close();
        header("Location: index.php");
        exit;
    } else {
        $conn->close();
        echo "<script>
            alert('Usuario o contraseña incorrectos.');
            window.location.href = 'login.html';
        </script>";
        exit;
    }
} else {
    http_response_code(405);
    echo "Método no permitido";
    exit;
}
?>
