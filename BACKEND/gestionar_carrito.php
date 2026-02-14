<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$accion = $_GET['accion'] ?? '';
$vinilo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

switch ($accion) {
    case 'agregar':
        if ($vinilo_id > 0) {
            // Verificar si el vinilo existe
            $stmt = $conn->prepare("SELECT id, nombre, precio, imagen FROM vinilos WHERE id = ? AND visible = 1 LIMIT 1");
            $stmt->bind_param("i", $vinilo_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $vinilo = $result->fetch_assoc();
                
                // Si ya está en el carrito, aumentar cantidad
                if (isset($_SESSION['carrito'][$vinilo_id])) {
                    $_SESSION['carrito'][$vinilo_id]['cantidad']++;
                } else {
                    // Agregar nuevo producto
                    $_SESSION['carrito'][$vinilo_id] = [
                        'id' => $vinilo['id'],
                        'nombre' => $vinilo['nombre'],
                        'precio' => $vinilo['precio'],
                        'imagen' => $vinilo['imagen'],
                        'cantidad' => 1
                    ];
                }
                
                header('Location: carrito.php?msg=agregado');
            } else {
                header('Location: catalogo.php?error=vinilo_no_existe');
            }
        } else {
            header('Location: catalogo.php');
        }
        exit;
        
    case 'eliminar':
        if ($vinilo_id > 0 && isset($_SESSION['carrito'][$vinilo_id])) {
            unset($_SESSION['carrito'][$vinilo_id]);
        }
        header('Location: carrito.php?msg=eliminado');
        exit;
        
    case 'actualizar':
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
        
        if ($vinilo_id > 0 && isset($_SESSION['carrito'][$vinilo_id])) {
            if ($cantidad > 0) {
                $_SESSION['carrito'][$vinilo_id]['cantidad'] = $cantidad;
            } else {
                unset($_SESSION['carrito'][$vinilo_id]);
            }
        }
        header('Location: carrito.php?msg=actualizado');
        exit;
        
    case 'vaciar':
        $_SESSION['carrito'] = [];
        header('Location: carrito.php?msg=vaciado');
        exit;
        
    default:
        header('Location: catalogo.php');
        exit;
}
?>
