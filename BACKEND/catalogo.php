<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito = $_SESSION['carrito'];

// ── Configuración de paginación ────────────────────────────────────
$vinilos_por_pagina = 8;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $vinilos_por_pagina;

// Búsqueda
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$where_busqueda = '';

if (!empty($busqueda)) {
    $where_busqueda = " AND (nombre LIKE ? OR descripcion LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
}

// Contar total de vinilos visibles con búsqueda
$count_sql = "SELECT COUNT(*) as total FROM vinilos WHERE visible = 1" . $where_busqueda;
$stmt_count = $conn->prepare($count_sql);
if (!empty($busqueda)) {
    $stmt_count->bind_param('ss', $busqueda_param, $busqueda_param);
}
$stmt_count->execute();
$total_row = $stmt_count->get_result()->fetch_assoc();
$total_vinilos = $total_row['total'];
$total_paginas = ceil($total_vinilos / $vinilos_por_pagina);

// Obtener vinilos de la página actual con prepared statement
$sql = "SELECT * FROM vinilos WHERE visible = 1" . $where_busqueda . " ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!empty($busqueda)) {
    $stmt->bind_param('ssii', $busqueda_param, $busqueda_param, $vinilos_por_pagina, $offset);
} else {
    $stmt->bind_param("ii", $vinilos_por_pagina, $offset);
}

$stmt->execute();
$vinilos = $stmt->get_result();

if (!$vinilos) {
    die("Error en query de vinilos: " . $conn->error);
}

$resenas_sql = "
    SELECT r.nombre, r.ciudad, r.comentario, r.fecha, v.nombre AS vinilo_nombre
    FROM resenas r
    JOIN vinilos v ON r.vinilo_id = v.id
    ORDER BY r.fecha DESC
";
$resenas = $conn->query($resenas_sql);
if (!$resenas) {
    die("Error en query de reseñas: " . $conn->error);
}

// Mensajes de estado
$resena_ok = isset($_GET['resena']) && $_GET['resena'] === 'ok';
$carrito_msg = isset($_GET['carrito']) && $_GET['carrito'] === 'ok';
$error_msg = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'campos_vacios':
            $error_msg = 'Por favor, rellena todos los campos del formulario.';
            break;
        case 'campos_muy_largos':
            $error_msg = 'Uno o más campos exceden la longitud máxima permitida.';
            break;
        case 'vinilo_no_existe':
            $error_msg = 'El vinilo seleccionado no existe.';
            break;
        case 'db_error':
            $error_msg = 'Error de base de datos. Inténtalo de nuevo más tarde.';
            break;
        case 'insert_failed':
            $error_msg = 'No se pudo guardar la reseña. Inténtalo de nuevo.';
            break;
        case 'metodo_invalido':
            $error_msg = 'Método de envío no válido.';
            break;
        default:
            $error_msg = 'Ha ocurrido un error. Inténtalo de nuevo.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Catálogo — Vinyl Lab</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Estilos -->
  <link rel="stylesheet" href="styles.css" />

  <style>
    .btn-resena {
      background-color: transparent;
      border: 1px solid #b8860b;
      color: #b8860b;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      font-size: 0.85rem;
      letter-spacing: 0.04em;
      padding: 0.4rem 0.75rem;
      border-radius: 4px;
      transition: background-color 0.2s ease, color 0.2s ease;
    }

    .btn-resena:hover {
      background-color: #b8860b;
      color: #fff;
    }

    /* Botón añadir al carrito */
    .btn-add-cart {
      background: linear-gradient(135deg, #28a745, #218838);
      color: white;
      border: none;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .btn-add-cart:hover {
      background: linear-gradient(135deg, #218838, #1e7e34);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.5);
      color: white;
    }
  </style>

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><text y='0.9em' font-size='400'>💿</text></svg>">
</head>

<body>

  <!-- Header -->
  <header class="main-header">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="header-left d-flex align-items-center">
        <img src="imagenes/VinylLab.png" alt="Logo Vinyl Lab" class="header-logo me-2">
        <h1 class="header-title">Vinyl Lab</h1>
      </div>

      <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['usuario'])): ?>
          <a href="https://vinyllabs-production.up.railway.app/gestionar_catalogo.php" class="btn-login-custom">Gestionar catálogo</a>
        <?php endif; ?>

        <a href="https://vinyl-labs.vercel.app" class="btn-login-custom">Inicio</a>

        <!-- Botón del carrito -->
        <a href="carrito.php" class="btn-login-custom position-relative">
          <i class="bi bi-cart3"></i> Carrito
          <?php if (!empty($carrito)): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= count($carrito) ?>
            </span>
          <?php endif; ?>
        </a>

        <button class="btn btn-hamburguesa" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </div>
  </header>

  <!-- Menú lateral offcanvas -->
  <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="menuLateral">
    <div class="offcanvas-header flex-column align-items-start w-100">
      <div class="logo-container">
        <img src="imagenes/VinylLab.png" alt="Logo Vinyl Lab" class="sidebar-logo">
      </div>
    </div>
    <div class="offcanvas-body">
      <nav class="nav flex-column">
        <a class="nav-link" href="https://vinyl-labs.vercel.app">Inicio</a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/catalogo.php">Catálogo</a>
        <a class="nav-link" href="#">Ofertas</a>
        <a class="nav-link" href="#">Contacto</a>
        <?php if (isset($_SESSION['usuario'])): ?>
          <a class="nav-link" href="gestionar_catalogo.php">Gestionar catálogo</a>
        <?php endif; ?>
      </nav>
    </div>
  </div>

  <!-- Contenido del catálogo -->
  <main class="main-content container py-5" style="margin-top: 120px;">

    <h2 class="mb-4 text-center">Catálogo de Vinilos</h2>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="catalogo.php" class="mb-4">
      <div class="input-group mx-auto" style="max-width: 600px;">
        <input type="text" name="buscar" class="form-control form-control-lg" placeholder="Buscar por nombre o descripción..." value="<?= htmlspecialchars($busqueda) ?>">
        <button class="btn btn-primary" type="submit" style="background: linear-gradient(135deg, #daa520, #b8860b); border: none;">
          <i class="bi bi-search"></i> Buscar
        </button>
        <?php if (!empty($busqueda)): ?>
          <a href="catalogo.php" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>

    <?php if (!empty($busqueda)): ?>
      <p class="text-center text-muted mb-4">
        Se encontraron <strong><?= $total_vinilos ?></strong> resultado<?= $total_vinilos != 1 ? 's' : '' ?> para "<strong><?= htmlspecialchars($busqueda) ?></strong>"
      </p>
    <?php endif; ?>

    <div class="row g-4">
      <?php if ($vinilos && $vinilos->num_rows > 0): ?>
        <?php while ($row = $vinilos->fetch_assoc()): ?>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm" style="background-color: rgba(255,255,255,0.9); border: none; cursor: pointer; transition: transform 0.3s ease;" onclick="window.location.href='detalle_vinilo.php?id=<?= $row['id'] ?>';" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
              <?php if (!empty($row['imagen'])): ?>
                <img src="<?= htmlspecialchars($row['imagen']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nombre']) ?>" style="object-fit: cover; height: 300px; width: 100%;" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'card-img-top bg-secondary d-flex align-items-center justify-content-center\' style=\'height: 300px;\'><i class=\'bi bi-music-note-beamed\' style=\'font-size: 4rem; color: rgba(255,255,255,0.5);\'></i></div>';">
              <?php else: ?>
                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 300px;">
                  <i class="bi bi-music-note-beamed" style="font-size: 4rem; color: rgba(255,255,255,0.5);"></i>
                </div>
              <?php endif; ?>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title" style="font-family: 'Bebas Neue', sans-serif;"><?= htmlspecialchars($row['nombre']) ?></h5>
                <p class="card-text mb-3"><?= number_format($row['precio'], 2, ',', '.') ?> €</p>
                <div class="mt-auto d-flex flex-column gap-2">
                  <div class="d-flex gap-2">
                    <a href="detalle_vinilo.php?id=<?= $row['id'] ?>" class="btn btn-sm flex-grow-1" style="background: linear-gradient(135deg, #daa520, #b8860b); color: white; font-weight: 600; border: none;" onclick="event.stopPropagation();">
                      <i class="bi bi-eye me-1"></i> Ver detalles
                    </a>
                    <a href="https://vinyl-labs.vercel.app/formulario.html?vinilo_id=<?= (int)$row['id'] ?>&vinilo_nombre=<?= urlencode($row['nombre']) ?>" class="btn btn-resena btn-sm" onclick="event.stopPropagation();">
                      <i class="bi bi-star"></i>
                    </a>
                  </div>
                  <a href="gestionar_carrito.php?accion=agregar&id=<?= $row['id'] ?>" class="btn btn-add-cart btn-sm w-100" onclick="event.stopPropagation();">
                    <i class="bi bi-cart-plus me-1"></i> Añadir al carrito
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p class="text-muted">No hay vinilos disponibles<?= !empty($busqueda) ? ' que coincidan con tu búsqueda' : ' en este momento' ?>.</p>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($total_paginas > 1): ?>
      <!-- Paginación -->
      <nav class="mt-5">
        <ul class="pagination justify-content-center">
          <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?pagina=<?= $pagina_actual - 1 ?><?= !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '' ?>">Anterior</a>
          </li>
          
          <?php
          $rango = 2;
          $inicio = max(1, $pagina_actual - $rango);
          $fin = min($total_paginas, $pagina_actual + $rango);
          
          if ($inicio > 1) {
            echo '<li class="page-item"><a class="page-link" href="?pagina=1' . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">1</a></li>';
            if ($inicio > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
          }
          
          for ($i = $inicio; $i <= $fin; $i++) {
            $active = $i === $pagina_actual ? 'active' : '';
            echo '<li class="page-item ' . $active . '"><a class="page-link" href="?pagina=' . $i . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">' . $i . '</a></li>';
          }
          
          if ($fin < $total_paginas) {
            if ($fin < $total_paginas - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            echo '<li class="page-item"><a class="page-link" href="?pagina=' . $total_paginas . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">' . $total_paginas . '</a></li>';
          }
          ?>
          
          <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
            <a class="page-link" href="?pagina=<?= $pagina_actual + 1 ?><?= !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '' ?>">Siguiente</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>