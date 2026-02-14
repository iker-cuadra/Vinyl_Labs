<?php
session_start();
require_once __DIR__ . '/conexion.php';

$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

$sql = "SELECT * FROM vinilos WHERE 1=1";

if (!empty($buscar)) {
    $sql .= " AND nombre LIKE ?";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!empty($buscar)) {
    $param = "%{$buscar}%";
    $stmt->bind_param('s', $param);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        $nombre = htmlspecialchars($row['nombre']);
        $precio = number_format($row['precio'], 2, ',', '.');
        $visible = (int)$row['visible'];
        $imagen = !empty($row['imagen']) ? htmlspecialchars($row['imagen']) : 'imagenes/default-vinyl.png';
        
        $checked = $visible ? 'checked' : '';
        ?>
        <tr>
            <td>
                <img src="<?= $imagen ?>" 
                     alt="<?= $nombre ?>" 
                     style="width:60px; height:60px; object-fit:cover; border-radius:8px;"
                     onerror="this.src='imagenes/default-vinyl.png'">
            </td>
            <td><?= $nombre ?></td>
            <td><?= $precio ?> €</td>
            <td>
                <div class="form-check form-switch d-flex justify-content-center">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        role="switch" 
                        <?= $checked ?>
                        onchange="toggleVisible(<?= $id ?>, this.checked)"
                        style="cursor: pointer; width: 50px; height: 25px;">
                </div>
            </td>
            <td>
                <button 
                    class="btn btn-sm btn-danger" 
                    onclick="eliminarVinilo(<?= $id ?>)"
                    title="Eliminar vinilo">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
        <?php
    }
} else {
    echo '<tr><td colspan="5" class="text-center">No se encontraron vinilos</td></tr>';
}

$stmt->close();
$conn->close();
?>