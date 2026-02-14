<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Verificar que el usuario esté autenticado (opcional, pero recomendado)
// if (!isset($_SESSION['usuario'])) {
//     echo '<tr><td colspan="5" class="text-center text-danger">Acceso no autorizado</td></tr>';
//     exit;
// }

$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Preparar consulta
if (!empty($buscar)) {
    $sql = "SELECT id, nombre, precio, imagen, visible FROM vinilos WHERE nombre LIKE ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $param = "%{$buscar}%";
    $stmt->bind_param("s", $param);
} else {
    $sql = "SELECT id, nombre, precio, imagen, visible FROM vinilos ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

// Generar filas de la tabla
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        $nombre = htmlspecialchars($row['nombre']);
        $precio = number_format($row['precio'], 2, ',', '.');
        $imagen = htmlspecialchars($row['imagen']);
        $visible = (int)$row['visible'];
        
        // Determinar si la imagen existe
        $img_html = '';
        if (!empty($imagen) && file_exists(__DIR__ . '/' . $imagen)) {
            $img_html = '<img src="' . $imagen . '" alt="' . $nombre . '" style="width:60px; height:60px; object-fit:cover; border-radius:8px;">';
        } else {
            $img_html = '<div style="width:60px; height:60px; background:#ddd; border-radius:8px; display:flex; align-items:center; justify-content:center;"><i class="bi bi-music-note" style="font-size:24px; color:#999;"></i></div>';
        }
        
        // Toggle de visibilidad
        $checked = $visible ? 'checked' : '';
        $toggle_html = '<div class="form-check form-switch d-flex justify-content-center">
                          <input class="form-check-input" type="checkbox" role="switch" 
                                 onchange="toggleVisible(' . $id . ', this.checked)" ' . $checked . '>
                        </div>';
        
        // Botones de acción
        $acciones_html = '
          <div class="d-flex gap-2 justify-content-center">
            <a href="detalle_vinilo.php?id=' . $id . '" 
               class="btn btn-sm btn-info" 
               title="Ver detalles"
               style="background: linear-gradient(135deg, #17a2b8, #138496); border:none;">
              <i class="bi bi-eye"></i>
            </a>
            <button onclick="eliminarVinilo(' . $id . ')" 
                    class="btn btn-sm btn-danger" 
                    title="Eliminar"
                    style="background: linear-gradient(135deg, #dc3545, #c82333); border:none;">
              <i class="bi bi-trash"></i>
            </button>
          </div>';
        
        echo '<tr>
                <td>' . $img_html . '</td>
                <td><strong>' . $nombre . '</strong></td>
                <td>' . $precio . ' €</td>
                <td>' . $toggle_html . '</td>
                <td>' . $acciones_html . '</td>
              </tr>';
    }
} else {
    echo '<tr>
            <td colspan="5" class="text-center text-muted py-4">
              <i class="bi bi-inbox" style="font-size:3rem; display:block; margin-bottom:10px;"></i>
              No se encontraron vinilos' . (!empty($buscar) ? ' que coincidan con "<strong>' . htmlspecialchars($buscar) . '</strong>"' : '') . '
            </td>
          </tr>';
}

$stmt->close();
$conn->close();
?>
