<?php
// Script de diagnóstico para verificar imágenes
require_once __DIR__ . '/conexion.php';

echo "<h2>Diagnóstico de Imágenes - Vinyl Lab</h2>";
echo "<style>
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
  th { background-color: #4CAF50; color: white; }
  .existe { color: green; font-weight: bold; }
  .noexiste { color: red; font-weight: bold; }
  img { max-width: 100px; }
</style>";

$result = $conn->query("SELECT id, nombre, imagen FROM vinilos");

echo "<table>";
echo "<tr><th>ID</th><th>Nombre</th><th>Ruta en BD</th><th>¿Existe?</th><th>Vista previa</th></tr>";

while ($row = $result->fetch_assoc()) {
    $imagen = $row['imagen'];
    $existe = file_exists(__DIR__ . '/' . $imagen);
    
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($imagen) . "</td>";
    echo "<td class='" . ($existe ? 'existe' : 'noexiste') . "'>" . ($existe ? 'SÍ' : 'NO') . "</td>";
    echo "<td>";
    if (!empty($imagen)) {
        echo "<img src='" . htmlspecialchars($imagen) . "' onerror=\"this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Crect fill=\'%23ddd\' width=\'100\' height=\'100\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3EERROR%3C/text%3E%3C/svg%3E'\">";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr><h3>Información del servidor:</h3>";
echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
echo "<p><strong>¿Existe carpeta uploads?:</strong> " . (is_dir(__DIR__ . '/uploads') ? 'SÍ' : 'NO') . "</p>";

if (is_dir(__DIR__ . '/uploads')) {
    echo "<h4>Archivos en /uploads:</h4><ul>";
    $files = scandir(__DIR__ . '/uploads');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
}
?>
