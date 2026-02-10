<?php
// ── CORS: permitir POST desde el frontend en Vercel ──────────────────────────
$allowed_origins = [
    'https://vinyl-labs.vercel.app',
    'https://vinyllabs-production.up.railway.app',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Responder al preflight OPTIONS y terminar
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
// ─────────────────────────────────────────────────────────────────────────────

session_start();
require_once __DIR__ . '/conexion.php';

// URLs base para redirecciones
define('URL_CATALOGO',   'https://vinyllabs-production.up.railway.app/catalogo.php');
define('URL_FORMULARIO', 'https://vinyl-labs.vercel.app/formulario.html');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_CATALOGO);
    exit;
}

// Recoger y sanear los datos
$vinilo_id  = isset($_POST['vinilo_id'])  ? (int)trim($_POST['vinilo_id'])   : 0;
$nombre     = isset($_POST['nombre'])     ? trim($_POST['nombre'])           : '';
$ciudad     = isset($_POST['ciudad'])     ? trim($_POST['ciudad'])           : '';
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario'])       : '';

// Validaciones básicas
if ($vinilo_id <= 0 || $nombre === '' || $ciudad === '' || $comentario === '') {
    header('Location: ' . URL_FORMULARIO . '?error=campos_vacios&vinilo_id=' . $vinilo_id);
    exit;
}

// Verificar que el vinilo existe y es visible
$stmt = $conn->prepare("SELECT id FROM vinilos WHERE id = ? AND visible = 1");
$stmt->bind_param('i', $vinilo_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    header('Location: ' . URL_CATALOGO . '?error=vinilo_no_encontrado');
    exit;
}
$stmt->close();

// Insertar la reseña en la BD
$insert = $conn->prepare(
    "INSERT INTO resenas (vinilo_id, nombre, ciudad, comentario) VALUES (?, ?, ?, ?)"
);
$insert->bind_param('isss', $vinilo_id, $nombre, $ciudad, $comentario);

if ($insert->execute()) {
    $insert->close();
    // ✅ Éxito: volver al catálogo con mensaje de confirmación
    header('Location: ' . URL_CATALOGO . '?resena=ok');
    exit;
} else {
    $error_db = $conn->error;
    $insert->close();
    // ❌ Error BD: volver al formulario con mensaje
    header('Location: ' . URL_FORMULARIO . '?error=db&vinilo_id=' . $vinilo_id);
    exit;
}