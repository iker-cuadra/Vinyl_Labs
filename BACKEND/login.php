<?php
session_start();
require_once __DIR__ . '/conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $pass   = $_POST['pass'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre = ? AND pass = ?");
    if (!$stmt) {
        die("Error prepare: " . $conn->error);
    }

    $stmt->bind_param("ss", $nombre, $pass);
    $stmt->execute();

    $resultado = $stmt->get_result(); // requiere mysqlnd
    if ($resultado && $resultado->num_rows === 1) {
        $_SESSION['usuario'] = $nombre;

        $stmt->close();
        $conn->close();

        // ✅ Redirige a Vercel (pon la página que quieras)
        header("Location: https://vinyl-labs-h7clqd0kd-iker-cuadras-projects.vercel.app/index.html");
        exit;
    } else {
        $stmt->close();
        $conn->close();

        echo "<script>
            alert('Usuario o contraseña incorrectos.');
            window.location.href = 'https://vinyl-labs-h7clqd0kd-iker-cuadras-projects.vercel.app/login.html';
        </script>";
        exit;
    }
}
