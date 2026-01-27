<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $pass = $_POST['pass'] ?? '';

    // Usar consultas preparadas para evitar SQL injection
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre = ? AND pass = ?");
    $stmt->bind_param("ss", $nombre, $pass);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $_SESSION['usuario'] = $nombre;
        $stmt->close();
        $conn->close();
        header("Location: ../FRONTEND/index.php");
        exit;
    } else {
        $stmt->close();
        $conn->close();
        echo "<script>
            alert('Usuario o contraseña incorrectos.');
            window.location.href = '../FRONTEND/login.html';
        </script>";
        exit;
    }
}
?>