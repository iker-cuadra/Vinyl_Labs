<?php
// Configurar la sesión ANTES de session_start()
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1'); // Solo HTTPS
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_lifetime', '86400'); // 24 horas

session_start();

// CORS headers
$allowed_origins = [
    'https://vinyl-labs.vercel.app',
    'http://localhost:3000',
    'http://localhost:5173'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: https://vinyl-labs.vercel.app');
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json');

// Manejar peticiones OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivo de conexión
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    $usuario = isset($input['usuario']) ? trim($input['usuario']) : '';
    $contrasena = isset($input['contrasena']) ? trim($input['contrasena']) : '';

    if (empty($usuario) || empty($contrasena)) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario y contraseña son requeridos'
        ]);
        exit();
    }

    try {
        // Buscar usuario en la base de datos
        $stmt = $conn->prepare("SELECT id, usuario, contrasena FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $row = $resultado->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($contrasena, $row['contrasena'])) {
                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);
                
                // Guardar datos en la sesión
                $_SESSION['usuario'] = $row['usuario'];
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['login_time'] = time();

                echo json_encode([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'usuario' => $row['usuario']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario o contraseña incorrectos'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos'
            ]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en el servidor: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}

$conn->close();
?>