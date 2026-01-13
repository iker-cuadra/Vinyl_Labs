<?php
session_start();
require 'conexion.php';

$usuario = $_POST['nombre'] ?? '';
$pass    = $_POST['pass'] ?? '';

// Consulta preparada
$stmt = $conn->prepare(
    "SELECT usuario, rol FROM usuarios WHERE usuario = ? AND password = ?"
);

$stmt->bind_param("ss", $usuario, $pass);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {

    $datos = $resultado->fetch_assoc();

    // Guardar datos en sesión
    $_SESSION['usuario'] = $datos['usuario'];
    $_SESSION['rol']     = $datos['rol'];

    // Redirigir a index.php (NO html)
    header("Location: index.php");
    exit;

} else {

    echo "<script>
        alert('Usuario o contraseña incorrectos');
        window.location.href='login.html';
    </script>";
}

$stmt->close();
$conn->close();
?>
