<?php
// Iniciar sesión y conectar a la base de datos
session_start();
require_once __DIR__ . '/conexion.php';

// Habilitar visualización de errores para depuración (comentar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogo.php?error=metodo_invalido');
    exit;
}

// Obtener y sanitizar datos del formulario
$vinilo_id = isset($_POST['vinilo_id']) ? (int)$_POST['vinilo_id'] : 0;
$nombre    = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$ciudad    = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

// Validar que todos los campos estén presentes
if ($vinilo_id <= 0 || empty($nombre) || empty($ciudad) || empty($comentario)) {
    header('Location: catalogo.php?error=campos_vacios');
    exit;
}

// Validar longitud de campos
if (strlen($nombre) > 100 || strlen($ciudad) > 100 || strlen($comentario) > 1000) {
    header('Location: catalogo.php?error=campos_muy_largos');
    exit;
}

// Verificar que el vinilo existe
$stmt_check = $conn->prepare("SELECT id FROM vinilos WHERE id = ? LIMIT 1");
if (!$stmt_check) {
    error_log("Error preparando consulta de verificación: " . $conn->error);
    header('Location: catalogo.php?error=db_error');
    exit;
}

$stmt_check->bind_param("i", $vinilo_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    $stmt_check->close();
    header('Location: catalogo.php?error=vinilo_no_existe');
    exit;
}
$stmt_check->close();

// Preparar la consulta de inserción
$stmt = $conn->prepare("INSERT INTO resenas (vinilo_id, nombre, ciudad, comentario, fecha) VALUES (?, ?, ?, ?, NOW())");

if (!$stmt) {
    error_log("Error preparando consulta de inserción: " . $conn->error);
    header('Location: catalogo.php?error=db_error');
    exit;
}

// Vincular parámetros
$stmt->bind_param("isss", $vinilo_id, $nombre, $ciudad, $comentario);

// Ejecutar la consulta
if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    // Redirigir al catálogo con mensaje de éxito
    header('Location: catalogo.php?resena=ok');
    exit;
} else {
    error_log("Error ejecutando inserción: " . $stmt->error);
    $stmt->close();
    $conn->close();
    
    // Redirigir con mensaje de error
    header('Location: catalogo.php?error=insert_failed');
    exit;
}
?>