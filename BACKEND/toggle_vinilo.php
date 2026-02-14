<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Establecer header JSON
header('Content-Type: application/json');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener y validar los datos
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$visible = isset($_POST['visible']) ? intval($_POST['visible']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

// Actualizar la visibilidad usando prepared statement
try {
    $stmt = $conn->prepare("UPDATE vinilos SET visible = ? WHERE id = ?");
    $stmt->bind_param("ii", $visible, $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Visibilidad actualizada correctamente',
            'id' => $id,
            'visible' => $visible
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>