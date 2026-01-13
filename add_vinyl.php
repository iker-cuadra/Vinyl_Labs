<?php
require 'admin_guard.php';
require 'conexion.php';

$nombre = trim($_POST['nombre'] ?? '');
$artista = trim($_POST['artista'] ?? '');
$anio = $_POST['anio'] ?? null;
$precio = $_POST['precio'] ?? null;
$imagen = trim($_POST['imagen'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

// BLINDAJE: normaliza rutas de Windows a ruta web
$imagen = str_replace("\\", "/", $imagen); // \ -> /
$imagen = preg_replace('#^C:/xampp/htdocs/Vinyl_Labs/#i', '', $imagen); // quita ruta local si la pegan
$imagen = ltrim($imagen, "/"); // evita /Imagenes/...

if ($nombre === '' || $artista === '') {
    header("Location: admin.php");
    exit;
}

$anio_int = ($anio === '' || $anio === null) ? null : (int)$anio;
$precio_float = ($precio === '' || $precio === null) ? null : (float)$precio;

// OJO: la columna es `año` (con ñ), por eso lleva backticks
$stmt = $conn->prepare("
    INSERT INTO vinilos (nombre, artista, descripcion, precio, `año`, imagen, visible)
    VALUES (?, ?, ?, ?, ?, ?, 1)
");

$stmt->bind_param("sssdis", $nombre, $artista, $descripcion, $precio_float, $anio_int, $imagen);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: admin.php");
exit;