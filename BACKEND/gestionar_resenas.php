<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Solo accesible con sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: https://vinyl-labs.vercel.app/login.html');
    exit;
}

// Eliminar reseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_POST['eliminar_id']);
    $ok = $conn->query("DELETE FROM resenas WHERE id = $id");
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

// Obtener filtros
$filtro_ciudad = isset($_GET['ciudad']) ? trim($_GET['ciudad']) : '';
$filtro_vinilo = isset($_GET['vinilo_id']) ? intval($_GET['vinilo_id']) : 0;

// Construir query con filtros
$where = [];
if (!empty($filtro_ciudad)) {
    $ciudad_escaped = $conn->real_escape_string($filtro_ciudad);
    $where[] = "r.ciudad LIKE '%$ciudad_escaped%'";
}
if ($filtro_vinilo > 0) {
    $where[] = "r.vinilo_id = $filtro_vinilo";
}
$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$resenas = $conn->query("
    SELECT r.id, r.nombre, r.ciudad, r.comentario, r.fecha, v.nombre AS vinilo_nombre, v.id AS vinilo_id
    FROM resenas r
    JOIN vinilos v ON r.vinilo_id = v.id
    $where_sql
    ORDER BY r.fecha DESC
");

// Obtener lista de vinilos para el select de filtro
$vinilos_list = $conn->query("SELECT id, nombre FROM vinilos ORDER BY nombre ASC");

// Obtener lista de ciudades únicas para el select
$ciudades_list = $conn->query("SELECT DISTINCT ciudad FROM resenas ORDER BY ciudad ASC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestionar Reseñas — Vinyl Lab</title>

  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="styles.css">

  <link rel="icon" href="data:image/svg+xml,
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
<text y='0.9em' font-size='400'>💿</text>
</svg>">

  <style>
    .btn-back-arrow {
      position: absolute;
      top: 20px;
      left: 20px;
      background: linear-gradient(135deg, #daa520, #b8860b);
      color: white;
      border: none;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      font-size: 1.3rem;
      box-shadow: 0 4px 15px rgba(184, 134, 11, 0.3);
      transition: all 0.3s ease;
      z-index: 10;
    }

    .btn-back-arrow:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 6px 20px rgba(184, 134, 11, 0.5);
      color: white;
    }

    .filtros-section {
      background: rgba(255, 243, 220, 0.6);
      border: 1px solid rgba(184, 134, 11, 0.2);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 24px;
    }

    .filtros-section label {
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      color: #5a2c0d;
      font-size: 0.9rem;
      margin-bottom: 6px;
    }

    .filtros-section .form-control,
    .filtros-section .form-select {
      border: 1.5px solid rgba(184, 134, 11, 0.35);
      font-family: 'Raleway', sans-serif;
      font-size: 0.9rem;
      border-radius: 8px;
    }

    .filtros-section .form-control:focus,
    .filtros-section .form-select:focus {
      border-color: #b8860b;
      box-shadow: 0 0 0 3px rgba(184, 134, 11, 0.15);
    }

    .btn-filtrar {
      background: linear-gradient(135deg, #daa520, #b8860b);
      color: white;
      border: none;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      padding: 10px 24px;
      border-radius: 8px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(184, 134, 11, 0.3);
    }

    .btn-filtrar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(184, 134, 11, 0.5);
      color: white;
    }

    .btn-limpiar {
      background: transparent;
      color: #b8860b;
      border: 1.5px solid #b8860b;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .btn-limpiar:hover {
      background: #b8860b;
      color: white;
    }

    .resena-row {
      transition: background 0.2s ease;
    }

    .resena-row:hover {
      background: rgba(255, 243, 220, 0.5);
    }

    .badge-vinilo {
      background: linear-gradient(135deg, rgba(184,134,11,0.2), rgba(139,105,20,0.15));
      border: 1px solid rgba(184,134,11,0.4);
      color: #7a5a00;
      font-family: 'Raleway', sans-serif;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 999px;
    }

    .badge-ciudad {
      background: rgba(90, 44, 13, 0.08);
      border: 1px solid rgba(90,44,13,0.2);
      color: #5a2c0d;
      font-family: 'Raleway', sans-serif;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 999px;
    }

    .comentario-text {
      font-family: 'Raleway', sans-serif;
      font-size: 0.9rem;
      color: #4a3a2a;
      line-height: 1.6;
      max-width: 400px;
    }

    .btn-eliminar-resena {
      background: transparent;
      border: 1.5px solid #dc3545;
      color: #dc3545;
      padding: 6px 14px;
      border-radius: 6px;
      font-size: 0.85rem;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      transition: all 0.25s ease;
    }

    .btn-eliminar-resena:hover {
      background: #dc3545;
      color: white;
    }

    .stats-bar {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .stat-chip {
      background: rgba(184,134,11,0.12);
      border: 1px solid rgba(184,134,11,0.3);
      border-radius: 8px;
      padding: 8px 16px;
      font-family: 'Raleway', sans-serif;
      font-size: 0.85rem;
      color: #5a2c0d;
    }

    .stat-chip strong {
      font-size: 1.2rem;
      color: #b8860b;
    }

    .fecha-text {
      font-family: 'Raleway', sans-serif;
      font-size: 0.8rem;
      color: #9a8a7a;
    }

    .nombre-text {
      font-family: 'Raleway', sans-serif;
      font-weight: 700;
      font-size: 0.92rem;
      color: #3a2a1a;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #9a7a5a;
      font-family: 'Raleway', sans-serif;
    }

    .empty-state i {
      font-size: 3rem;
      opacity: 0.4;
      display: block;
      margin-bottom: 15px;
    }
  </style>
</head>

<body style="background-image: url('imagenes/FondoMadera.avif'); background-attachment: fixed;">

  <!-- HEADER -->
  <header class="main-header">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="header-left d-flex align-items-center">
        <img src="imagenes/VinylLab.png" class="header-logo me-2">
        <h1 class="header-title">Vinyl Lab</h1>
      </div>
      <div class="d-flex align-items-center gap-2">
        <a href="https://vinyl-labs.vercel.app" class="btn-login-custom">Inicio</a>
        <a href="https://vinyllabs-production.up.railway.app/logout.php" class="btn-login-custom">Cerrar sesión</a>
        <button class="btn btn-hamburguesa" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" id="btnHamburguesa">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </div>
  </header>

  <!-- MENÚ LATERAL -->
  <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="menuLateral">
    <div class="offcanvas-header">
      <img src="imagenes/VinylLab.png" class="sidebar-logo">
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <nav class="nav flex-column">
        <a class="nav-link" href="https://vinyl-labs.vercel.app">
          <i class="bi bi-house-door me-2"></i> Inicio
        </a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/catalogo.php">
          <i class="bi bi-music-note-list me-2"></i> Catálogo
        </a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/add_vinilos.php">
          <i class="bi bi-plus-circle me-2"></i> Añadir vinilo
        </a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/gestionar_catalogo.php">
          <i class="bi bi-gear me-2"></i> Gestionar catálogo
        </a>
        <a class="nav-link active" href="https://vinyllabs-production.up.railway.app/gestionar_resenas.php">
          <i class="bi bi-chat-square-text me-2"></i> Gestionar reseñas
        </a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/logout.php">
          <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
        </a>
      </nav>
    </div>
  </div>

  <!-- CONTENIDO -->
  <main class="container py-5" style="margin-top: 130px;">
    <div class="card shadow-lg mx-auto p-4"
      style="max-width: 1200px; background-color: rgba(255,243,230,0.97); border-radius: 16px; position: relative;">

      <!-- Flecha de regreso -->
      <a href="https://vinyllabs-production.up.railway.app/gestionar_catalogo.php"
         class="btn-back-arrow" title="Volver a gestionar catálogo">
        <i class="bi bi-arrow-left"></i>
      </a>

      <h2 class="text-center mb-2" style="font-family:'Bebas Neue'; color:#5a2c0d; font-size: 2.2rem;">
        <i class="bi bi-chat-square-text me-2"></i> Gestionar Reseñas
      </h2>
      <p class="text-center mb-4" style="font-family:'Raleway'; color:#9a7a5a; font-size:0.9rem;">
        Consulta, filtra y elimina las opiniones de los clientes
      </p>

      <!-- ESTADÍSTICAS -->
      <?php
        $total_resenas = $conn->query("SELECT COUNT(*) as total FROM resenas")->fetch_assoc()['total'];
        $total_ciudades = $conn->query("SELECT COUNT(DISTINCT ciudad) as total FROM resenas")->fetch_assoc()['total'];
        $total_con_filtro = $resenas ? $resenas->num_rows : 0;
      ?>
      <div class="stats-bar">
        <div class="stat-chip"><strong><?= $total_resenas ?></strong> reseñas en total</div>
        <div class="stat-chip"><strong><?= $total_ciudades ?></strong> ciudades distintas</div>
        <?php if (!empty($filtro_ciudad) || $filtro_vinilo > 0): ?>
          <div class="stat-chip"><strong><?= $total_con_filtro ?></strong> resultados filtrados</div>
        <?php endif; ?>
      </div>

      <!-- FILTROS -->
      <form method="GET" action="" class="filtros-section">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label><i class="bi bi-geo-alt me-1"></i> Filtrar por ciudad</label>
            <select name="ciudad" class="form-select">
              <option value="">— Todas las ciudades —</option>
              <?php if ($ciudades_list): while ($c = $ciudades_list->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($c['ciudad']) ?>"
                  <?= $filtro_ciudad === $c['ciudad'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['ciudad']) ?>
                </option>
              <?php endwhile; endif; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label><i class="bi bi-disc me-1"></i> Filtrar por vinilo</label>
            <select name="vinilo_id" class="form-select">
              <option value="0">— Todos los vinilos —</option>
              <?php if ($vinilos_list): while ($v = $vinilos_list->fetch_assoc()): ?>
                <option value="<?= $v['id'] ?>"
                  <?= $filtro_vinilo === (int)$v['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($v['nombre']) ?>
                </option>
              <?php endwhile; endif; ?>
            </select>
          </div>
          <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn-filtrar">
              <i class="bi bi-funnel me-1"></i> Filtrar
            </button>
            <a href="gestionar_resenas.php" class="btn-limpiar">
              <i class="bi bi-x-circle me-1"></i> Limpiar
            </a>
          </div>
        </div>
      </form>

      <!-- TABLA DE RESEÑAS -->
      <div class="table-responsive">
        <table class="table align-middle">
          <thead style="background-color: #3d2714; color: white;">
            <tr>
              <th style="width:160px;">Cliente</th>
              <th>Comentario</th>
              <th style="width:160px;">Vinilo</th>
              <th style="width:110px;">Fecha</th>
              <th style="width:90px;" class="text-center">Acción</th>
            </tr>
          </thead>
          <tbody id="tablaResenas">
            <?php if ($resenas && $resenas->num_rows > 0): ?>
              <?php while ($r = $resenas->fetch_assoc()): ?>
                <tr class="resena-row" id="fila-<?= $r['id'] ?>">
                  <td>
                    <div class="nombre-text"><?= htmlspecialchars($r['nombre']) ?></div>
                    <span class="badge-ciudad mt-1 d-inline-block">
                      <i class="bi bi-geo-alt-fill me-1" style="font-size:0.65rem;"></i>
                      <?= htmlspecialchars($r['ciudad']) ?>
                    </span>
                  </td>
                  <td>
                    <p class="comentario-text mb-0"><?= htmlspecialchars($r['comentario']) ?></p>
                  </td>
                  <td>
                    <span class="badge-vinilo">💿 <?= htmlspecialchars($r['vinilo_nombre']) ?></span>
                  </td>
                  <td>
                    <span class="fecha-text">
                      <?= date('d/m/Y', strtotime($r['fecha'])) ?><br>
                      <?= date('H:i', strtotime($r['fecha'])) ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <button class="btn-eliminar-resena"
                            onclick="eliminarResena(<?= $r['id'] ?>)"
                            title="Eliminar reseña">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5">
                  <div class="empty-state">
                    <i class="bi bi-chat-square-x"></i>
                    <?php if (!empty($filtro_ciudad) || $filtro_vinilo > 0): ?>
                      No hay reseñas que coincidan con los filtros aplicados.
                    <?php else: ?>
                      Aún no hay reseñas registradas.
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const offcanvasEl = document.getElementById('menuLateral');
    const btnHamb = document.getElementById('btnHamburguesa');
    offcanvasEl.addEventListener('show.bs.offcanvas', () => btnHamb.classList.add('active'));
    offcanvasEl.addEventListener('hidden.bs.offcanvas', () => btnHamb.classList.remove('active'));

    function eliminarResena(id) {
      if (!confirm('¿Estás seguro de que quieres eliminar esta reseña?')) return;

      fetch('gestionar_resenas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'eliminar_id=' + id
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Eliminar la fila con animación
          const fila = document.getElementById('fila-' + id);
          fila.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
          fila.style.opacity = '0';
          fila.style.transform = 'translateX(-20px)';
          setTimeout(() => fila.remove(), 300);

          // Mostrar mensaje
          const msg = document.createElement('div');
          msg.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
          msg.style.zIndex = '9999';
          msg.innerHTML = `<i class="bi bi-check-circle me-2"></i> Reseña eliminada correctamente
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
          document.body.appendChild(msg);
          setTimeout(() => msg.remove(), 3000);
        } else {
          alert('Error al eliminar la reseña.');
        }
      })
      .catch(() => alert('Error de conexión al eliminar la reseña.'));
    }
  </script>

</body>
</html>
