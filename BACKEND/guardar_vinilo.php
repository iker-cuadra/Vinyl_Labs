<?php
session_start();
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $anio = intval($_POST['anio'] ?? 0);

    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        die("Error al subir la imagen.");
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES['imagen']['tmp_name'];
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $safeName = uniqid('vinilo_') . '.' . $ext;
    $destination = $uploadDir . $safeName;

    if (!move_uploaded_file($tmpName, $destination)) {
        die("Error al mover la imagen.");
    }

    $rutaDB = 'uploads/' . $safeName;

 $stmt = $conn->prepare("
    INSERT INTO vinilos (nombre, descripcion, precio, anio, imagen, visible)
    VALUES (?, ?, ?, ?, ?, 1)
");

    // ✅ AQUÍ ESTABA EL ERROR
    $stmt->bind_param("ssdis", $nombre, $descripcion, $precio, $anio, $rutaDB);

    if (!$stmt->execute()) {
        die("Error BD: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    header("Location: https://vinyllabs-production.up.railway.app/catalogo.php");
    exit;

} else {
    http_response_code(405);
    echo "Método no permitido";
    exit;
}
