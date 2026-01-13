<?php
session_start();
require 'conexion.php';

// Traer solo visibles
$stmt = $conn->prepare("SELECT id, nombre, artista, descripcion, precio, `año`, imagen
                        FROM vinilos
                        WHERE visible = 1
                        ORDER BY id DESC");
$stmt->execute();
$vinilos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Base URL del proyecto (para que las rutas tipo Imagenes/... funcionen siempre)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

function img_src(string $raw, string $base): string
{
    $raw = trim($raw);
    if ($raw === '') return '';
    // Si es URL completa, déjala igual
    if (preg_match('#^https?://#i', $raw)) return $raw;

    // Normaliza slashes y elimina / inicial
    $raw = str_replace("\\", "/", $raw);
    $raw = ltrim($raw, "/");

    // Prefija con base del proyecto
    return $base . $raw;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Vinyl Lab</title>

    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <header class="main-header">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="header-left d-flex align-items-center">
                <img src="Imagenes/VinylLab.png" alt="Logo Vinyl Lab" class="header-logo me-2">
                <h1 class="header-title">Vinyl Lab</h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <span class="text-light">Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>

                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                        <a href="admin.php" class="btn btn-warning btn-sm">Admin</a>
                    <?php endif; ?>

                    <a href="logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
                <?php else: ?>
                    <a href="login.html" class="btn btn-primary btn-sm">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main-content container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Catálogo</h2>
            <a href="index.php" class="btn btn-secondary btn-sm">Volver al inicio</a>
        </div>

        <?php if (empty($vinilos)): ?>
            <div class="alert alert-warning">Aún no hay vinilos disponibles.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($vinilos as $v): ?>
                    <?php
                    $src = img_src($v['imagen'] ?? '', $base);
                    ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <?php if ($src !== ''): ?>
                                <img
                                    src="<?php echo htmlspecialchars($src); ?>"
                                    class="card-img-top"
                                    alt="<?php echo htmlspecialchars($v['nombre'] ?? 'Vinilo'); ?>"
                                    style="height:220px; object-fit:cover;">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($v['nombre'] ?? ''); ?></h5>
                                <p class="card-text mb-1"><strong>Artista:</strong> <?php echo htmlspecialchars($v['artista'] ?? ''); ?></p>

                                <?php if (!empty($v['año'])): ?>
                                    <p class="card-text mb-1"><strong>Año:</strong> <?php echo htmlspecialchars($v['año']); ?></p>
                                <?php endif; ?>

                                <?php if ($v['precio'] !== null && $v['precio'] !== ''): ?>
                                    <p class="card-text mb-2"><strong>Precio:</strong> <?php echo htmlspecialchars($v['precio']); ?> €</p>
                                <?php endif; ?>

                                <?php if (!empty($v['descripcion'])): ?>
                                    <p class="card-text small text-muted"><?php echo nl2br(htmlspecialchars($v['descripcion'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>