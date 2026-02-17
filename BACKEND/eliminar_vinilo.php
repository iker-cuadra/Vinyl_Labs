<?php
session_start();
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

// Leer id desde POST (no GET)
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no válido']);
    exit;
}

// Obtener ruta de imagen antes de borrar
$res = $conn->query("SELECT imagen FROM vinilos WHERE id = $id");
if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Vinilo no encontrado']);
    exit;
}

$img = $res->fetch_assoc()['imagen'];

// Borrar imagen del servidor si existe
if (!empty($img) && file_exists($img)) {
    unlink($img);
}

// Borrar registro de la base de datos
$ok = $conn->query("DELETE FROM vinilos WHERE id = $id");

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $conn->error]);
}
exit;