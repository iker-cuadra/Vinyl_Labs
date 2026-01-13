<?php
require 'admin_guard.php';
require 'conexion.php';

$result = $conn->query("SELECT id, nombre, artista, `año`, precio, imagen, visible FROM vinilos ORDER BY id DESC");
$vinilos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Catálogo</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Gestión de Catálogo</h1>
            <div class="d-flex gap-2 align-items-center">
                <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <a class="btn btn-secondary btn-sm" href="index.php">Volver</a>
                <a class="btn btn-danger btn-sm" href="logout.php">Cerrar sesión</a>
            </div>
        </div>

        <!-- Añadir vinilo -->
        <div class="card mb-4">
            <div class="card-header">Añadir vinilo</div>
            <div class="card-body">
                <form action="add_vinyl.php" method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input name="nombre" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Artista</label>
                        <input name="artista" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Año</label>
                        <input name="anio" type="number" class="form-control" min="1900" max="2100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Precio (€)</label>
                        <input name="precio" type="number" step="0.01" class="form-control" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Imagen (ruta o URL)</label>
                        <input name="imagen" class="form-control" placeholder="Imagenes/Morgan_wallen.jpg o https://...">
                        <small class="text-muted">Usa ruta web (relativa) o URL. No uses C:\...</small>

                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-success" type="submit">Guardar</button>
                        <button class="btn btn-outline-secondary" type="reset">Limpiar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista -->
        <div class="card">
            <div class="card-header">Vinilos</div>
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Artista</th>
                            <th>Año</th>
                            <th>Precio</th>
                            <th>Visible</th>
                            <th style="width:220px;">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($vinilos as $v): ?>
                            <?php $visible = (int)($v['visible'] ?? 1); ?>
                            <tr>
                                <td><?php echo (int)$v['id']; ?></td>

                                <td>
                                    <?php if (!empty($v['imagen'])): ?>
                                        <img
                                            src="<?php echo htmlspecialchars($v['imagen']); ?>"
                                            alt="<?php echo htmlspecialchars($v['nombre']); ?>"
                                            style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
                                    <?php else: ?>
                                        <span class="text-muted">Sin imagen</span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo htmlspecialchars($v['nombre'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($v['artista'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($v['año'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($v['precio'] ?? ''); ?> €</td>
                                <td><?php echo $visible ? 'Sí' : 'No'; ?></td>

                                <td class="d-flex gap-2">

                                    <form action="toggle_visible.php" method="POST">
                                        <input type="hidden" name="id" value="<?php echo (int)$v['id']; ?>">
                                        <button class="btn btn-warning btn-sm" type="submit">
                                            <?php echo $visible ? 'Ocultar' : 'Mostrar'; ?>
                                        </button>
                                    </form>

                                    <form action="delete_vinyl.php" method="POST" onsubmit="return confirm('¿Eliminar este vinilo?');">
                                        <input type="hidden" name="id" value="<?php echo (int)$v['id']; ?>">
                                        <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>