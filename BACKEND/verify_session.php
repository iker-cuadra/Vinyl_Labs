<?php
session_start();

// Permitir peticiones desde cualquier origen (CORS)
header('Access-Control-Allow-Origin: https://vinyl-labs.vercel.app');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Verificar si existe una sesión activa
$sesion_activa = isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);

// Devolver respuesta en JSON
echo json_encode([
    'sesion_activa' => $sesion_activa,
    'usuario' => $sesion_activa ? $_SESSION['usuario'] : null
]);
?>