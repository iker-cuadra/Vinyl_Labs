<?php
// Script de diagnóstico - VER QUÉ BASE DE DATOS ESTAMOS USANDO

echo "<h2>🔍 Diagnóstico de Conexión</h2>";
echo "<hr>";

// Variables de entorno
echo "<h3>Variables de entorno:</h3>";
echo "<pre>";
echo "MYSQLHOST: " . (getenv('MYSQLHOST') ?: 'NO DEFINIDA - usando localhost') . "\n";
echo "MYSQLUSER: " . (getenv('MYSQLUSER') ?: 'NO DEFINIDA - usando root') . "\n";
echo "MYSQLDATABASE: " . (getenv('MYSQLDATABASE') ?: 'NO DEFINIDA - usando login_vinyl') . "\n";
echo "MYSQLPORT: " . (getenv('MYSQLPORT') ?: 'NO DEFINIDA - usando 3306') . "\n";
echo "</pre>";

// Conexión real
require_once 'conexion.php';

echo "<h3>Conexión actual:</h3>";
echo "<pre>";
echo "Base de datos seleccionada: <strong>" . $base_datos . "</strong>\n";
echo "Host: " . $host . "\n";
echo "Usuario: " . $usuario . "\n";
echo "Puerto: " . $puerto . "\n";
echo "</pre>";

// Listar todas las bases de datos disponibles
echo "<h3>Bases de datos disponibles:</h3>";
$databases = $conn->query("SHOW DATABASES");
echo "<ul>";
while ($row = $databases->fetch_array()) {
    $db_name = $row[0];
    $is_current = ($db_name === $base_datos) ? ' <strong style="color:green;">← ACTUAL</strong>' : '';
    echo "<li>" . $db_name . $is_current . "</li>";
}
echo "</ul>";

// Tablas en la base de datos actual
echo "<h3>Tablas en la base de datos '$base_datos':</h3>";
$tables = $conn->query("SHOW TABLES");
if ($tables && $tables->num_rows > 0) {
    echo "<ul>";
    while ($row = $tables->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>❌ No hay tablas o no se pudo consultar</p>";
}

// Verificar tabla resenas específicamente
echo "<h3>¿Existe la tabla 'resenas'?</h3>";
$check = $conn->query("SHOW TABLES LIKE 'resenas'");
if ($check && $check->num_rows > 0) {
    echo "<p style='color:green;'>✅ SÍ existe la tabla 'resenas'</p>";
    
    // Mostrar estructura
    echo "<h4>Estructura de la tabla:</h4>";
    $structure = $conn->query("DESCRIBE resenas");
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar registros
    $count = $conn->query("SELECT COUNT(*) as total FROM resenas");
    $total = $count->fetch_assoc()['total'];
    echo "<p><strong>Total de reseñas:</strong> $total</p>";
    
} else {
    echo "<p style='color:red;'>❌ NO existe la tabla 'resenas' en esta base de datos</p>";
    echo "<p><strong>SOLUCIÓN:</strong> La tabla está en otra base de datos. Necesitas cambiar la variable de entorno MYSQLDATABASE en Railway.</p>";
}

// Verificar tabla vinilos
echo "<h3>¿Existe la tabla 'vinilos'?</h3>";
$check_vinilos = $conn->query("SHOW TABLES LIKE 'vinilos'");
if ($check_vinilos && $check_vinilos->num_rows > 0) {
    echo "<p style='color:green;'>✅ SÍ existe la tabla 'vinilos'</p>";
} else {
    echo "<p style='color:red;'>❌ NO existe la tabla 'vinilos'</p>";
}

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    h2 { color: #333; }
    h3 { color: #666; margin-top: 30px; }
    pre { background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd; }
    ul { background: #fff; padding: 20px 40px; border-radius: 5px; border: 1px solid #ddd; }
    table { background: #fff; margin: 10px 0; }
</style>