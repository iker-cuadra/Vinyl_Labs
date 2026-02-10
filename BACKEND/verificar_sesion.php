<?php
session_start();

// Lista de orígenes permitidos
$allowed_origins = [
    'https://vinyl-labs.vercel.app',
    'http://localhost:3000',
    'http://localhost:5173',
    'http://127.0.0.1:5500',
    'http://localhost'
];

// Obtener el origen de la petición
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Verificar si el origen está en la lista de permitidos
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    // Si no hay origen o no está permitido, usar el principal
    header('Access-Control-Allow-Origin: https://vinyl-labs.vercel.app');
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json');

// Manejar peticiones OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar si existe una sesión activa
$sesion_activa = isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);

// Devolver respuesta en JSON con información adicional para debugging
echo json_encode([
    'sesion_activa' => $sesion_activa,
    'usuario' => $sesion_activa ? $_SESSION['usuario'] : null,
    'session_id' => session_id(), // Para debugging
    'debug' => [
        'session_data' => $_SESSION,
        'cookies' => $_COOKIE
    ]
]);
?>