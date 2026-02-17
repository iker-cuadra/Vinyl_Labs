<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: https://vinyl-labs.vercel.app/login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_POST['eliminar_id']);
    $ok = $conn->query("DELETE FROM resenas WHERE id = $id");
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

$filtro_ciudad = isset($_GET['ciudad']) ? trim($_GET['ciudad']) : '';
$filtro_vinilo = isset($_GET['vinilo_id']) ? intval($_GET['vinilo_id']) : 0;

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

$vinilos_list = $conn->query("SELECT id, nombre FROM vinilos ORDER BY nombre ASC");
$ciudades_list = $conn->query("SELECT DISTINCT ciudad FROM resenas ORDER BY ciudad ASC");

$total_resenas = $conn->query("SELECT COUNT(*) as total FROM resenas")->fetch_assoc()['total'];
$total_ciudades = $conn->query("SELECT COUNT(DISTINCT ciudad) as total FROM resenas")->fetch_assoc()['total'];
$total_con_filtro = $resenas ? $resenas->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestionar Reseñas — Vinyl Lab</title>

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="styles.css">

  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><text y='0.9em' font-size='400'>💿</text></svg>">

  <style>
    :root {
      --ink:       #1a0f00;
      --espresso:  #3d1f00;
      --caramel:   #8b5e2a;
      --gold:      #c9952a;
      --gold-light:#e8b84b;
      --cream:     #fdf5e6;
      --cream-dark:#f5e8cc;
      --mist:      rgba(255,248,235,0.85);
      --groove:    #2a1500;
      --wax:       rgba(201,149,42,0.12);
      --red-soft:  #c0392b;
    }

    * { box-sizing: border-box; }

    body {
      background-image: url('imagenes/FondoMadera.avif');
      background-attachment: fixed;
      font-family: 'DM Sans', sans-serif;
      color: var(--ink);
    }

    /* ─── BACK BUTTON ─── */
    .btn-back-arrow {
      position: absolute;
      top: 22px;
      left: 22px;
      background: linear-gradient(135deg, var(--gold-light), var(--gold));
      color: white;
      border: none;
      width: 42px;
      height: 42px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      font-size: 1.2rem;
      box-shadow: 0 4px 14px rgba(201,149,42,0.35);
      transition: all 0.3s ease;
      z-index: 10;
    }
    .btn-back-arrow:hover {
      transform: translateY(-2px) scale(1.08);
      box-shadow: 0 7px 22px rgba(201,149,42,0.55);
      color: white;
    }

    /* ─── CARD WRAPPER ─── */
    .panel-card {
      max-width: 1200px;
      margin: 0 auto;
      background: var(--mist);
      backdrop-filter: blur(14px);
      border-radius: 20px;
      border: 1px solid rgba(201,149,42,0.25);
      box-shadow: 0 24px 80px rgba(30,10,0,0.22), 0 2px 0 rgba(255,255,255,0.6) inset;
      padding: 40px 36px 36px;
      position: relative;
      overflow: hidden;
    }

    /* top-left groove circle decoration */
    .panel-card::before {
      content: '';
      position: absolute;
      top: -120px;
      right: -120px;
      width: 340px;
      height: 340px;
      border-radius: 50%;
      background: conic-gradient(
        from 0deg,
        transparent 0%, transparent 10%,
        rgba(201,149,42,0.06) 10%, rgba(201,149,42,0.06) 20%,
        transparent 20%, transparent 30%,
        rgba(201,149,42,0.04) 30%, rgba(201,149,42,0.04) 40%,
        transparent 40%
      );
      pointer-events: none;
    }

    /* ─── PAGE TITLE ─── */
    .page-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2.6rem;
      color: var(--espresso);
      letter-spacing: 1.5px;
      line-height: 1;
      margin-bottom: 4px;
    }
    .page-subtitle {
      font-family: 'DM Sans', sans-serif;
      color: var(--caramel);
      font-size: 0.88rem;
      font-weight: 400;
    }

    /* ─── STATS ROW ─── */
    .stats-row {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
      margin: 24px 0 22px;
    }
    .stat-pill {
      display: flex;
      align-items: center;
      gap: 10px;
      background: linear-gradient(135deg, rgba(201,149,42,0.10), rgba(201,149,42,0.04));
      border: 1px solid rgba(201,149,42,0.30);
      border-radius: 12px;
      padding: 10px 18px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(201,149,42,0.18);
    }
    .stat-pill .stat-number {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.7rem;
      color: var(--gold);
      line-height: 1;
    }
    .stat-pill .stat-label {
      font-size: 0.78rem;
      font-weight: 500;
      color: var(--espresso);
      line-height: 1.3;
    }
    .stat-pill .stat-icon {
      font-size: 1.1rem;
      color: var(--gold);
      opacity: 0.7;
    }

    /* ─── FILTER SECTION ─── */
    .filtros-panel {
      background: linear-gradient(135deg, rgba(253,245,230,0.8), rgba(245,232,204,0.6));
      border: 1px solid rgba(201,149,42,0.22);
      border-radius: 14px;
      padding: 20px 22px;
      margin-bottom: 26px;
    }
    .filtros-panel label {
      font-weight: 600;
      font-size: 0.78rem;
      color: var(--espresso);
      letter-spacing: 0.5px;
      text-transform: uppercase;
      display: block;
      margin-bottom: 6px;
    }
    .filtros-panel .form-select {
      border: 1.5px solid rgba(201,149,42,0.30);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.88rem;
      border-radius: 10px;
      background-color: rgba(255,252,245,0.9);
      color: var(--espresso);
      padding: 9px 14px;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .filtros-panel .form-select:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(201,149,42,0.15);
      outline: none;
    }

    .btn-filtrar {
      background: linear-gradient(135deg, var(--gold-light), var(--gold));
      color: white;
      border: none;
      font-family: 'DM Sans', sans-serif;
      font-weight: 600;
      font-size: 0.88rem;
      padding: 10px 22px;
      border-radius: 10px;
      transition: all 0.25s ease;
      box-shadow: 0 4px 14px rgba(201,149,42,0.35);
      letter-spacing: 0.3px;
    }
    .btn-filtrar:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 22px rgba(201,149,42,0.5);
      color: white;
    }
    .btn-limpiar {
      background: transparent;
      color: var(--caramel);
      border: 1.5px solid rgba(201,149,42,0.5);
      font-family: 'DM Sans', sans-serif;
      font-weight: 600;
      font-size: 0.88rem;
      padding: 10px 18px;
      border-radius: 10px;
      transition: all 0.25s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-limpiar:hover {
      background: var(--caramel);
      color: white;
      border-color: var(--caramel);
    }

    /* ─── TABLE ─── */
    .resenas-table-wrap {
      border-radius: 14px;
      overflow: hidden;
      border: 1px solid rgba(201,149,42,0.18);
      box-shadow: 0 4px 24px rgba(30,10,0,0.07);
    }
    .resenas-table-wrap table {
      margin: 0;
      border-collapse: collapse;
    }
    .resenas-table-wrap thead tr {
      background: linear-gradient(90deg, var(--groove) 0%, #3d1f00 100%);
    }
    .resenas-table-wrap thead th {
      font-family: 'DM Sans', sans-serif;
      font-weight: 600;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: rgba(255,248,220,0.85);
      padding: 14px 16px;
      border: none;
    }
    .resenas-table-wrap tbody tr {
      border-bottom: 1px solid rgba(201,149,42,0.10);
      transition: background 0.2s ease;
    }
    .resenas-table-wrap tbody tr:last-child {
      border-bottom: none;
    }
    .resenas-table-wrap tbody tr:hover {
      background: rgba(253,245,230,0.75);
    }
    .resenas-table-wrap tbody td {
      padding: 16px 16px;
      vertical-align: middle;
      border: none;
      background: transparent;
    }

    /* avatar + name cell */
    .user-cell {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .user-avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--gold-light), var(--caramel));
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1rem;
      color: white;
      flex-shrink: 0;
      box-shadow: 0 2px 8px rgba(201,149,42,0.30);
    }
    .user-info .nombre-text {
      font-weight: 600;
      font-size: 0.88rem;
      color: var(--espresso);
      line-height: 1.2;
    }

    /* city badge */
    .badge-ciudad {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: rgba(61,31,0,0.07);
      border: 1px solid rgba(61,31,0,0.15);
      color: var(--espresso);
      font-size: 0.70rem;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 999px;
      margin-top: 4px;
      letter-spacing: 0.2px;
    }

    /* comment */
    .comentario-cell {
      font-size: 0.875rem;
      color: #4a3520;
      line-height: 1.6;
      max-width: 380px;
      font-style: italic;
      position: relative;
      padding-left: 18px;
    }
    .comentario-cell::before {
      content: '"';
      position: absolute;
      left: 2px;
      top: -6px;
      font-family: 'Playfair Display', serif;
      font-size: 2.2rem;
      color: var(--gold);
      opacity: 0.35;
      line-height: 1;
    }

    /* vinyl badge */
    .badge-vinilo {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: linear-gradient(135deg, rgba(201,149,42,0.16), rgba(139,94,42,0.10));
      border: 1px solid rgba(201,149,42,0.35);
      color: #6b4700;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 5px 11px;
      border-radius: 8px;
      max-width: 150px;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
      display: block;
    }

    /* date */
    .fecha-wrap {
      text-align: center;
    }
    .fecha-day {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.4rem;
      color: var(--espresso);
      line-height: 1;
    }
    .fecha-month {
      font-size: 0.70rem;
      font-weight: 600;
      text-transform: uppercase;
      color: var(--gold);
      letter-spacing: 0.5px;
    }
    .fecha-time {
      font-size: 0.68rem;
      color: #a08060;
      margin-top: 2px;
    }

    /* delete button */
    .btn-eliminar {
      background: transparent;
      border: 1.5px solid rgba(192,57,43,0.4);
      color: var(--red-soft);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      transition: all 0.25s ease;
      cursor: pointer;
    }
    .btn-eliminar:hover {
      background: var(--red-soft);
      border-color: var(--red-soft);
      color: white;
      transform: scale(1.12) rotate(8deg);
      box-shadow: 0 4px 14px rgba(192,57,43,0.35);
    }

    /* empty state */
    .empty-state {
      padding: 70px 20px;
      text-align: center;
      color: var(--caramel);
      font-size: 0.9rem;
    }
    .empty-state .empty-icon {
      font-size: 3rem;
      display: block;
      margin-bottom: 14px;
      opacity: 0.35;
    }

    /* active filter indicator */
    .filter-active-bar {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 14px;
      padding: 8px 14px;
      background: rgba(201,149,42,0.10);
      border-radius: 8px;
      font-size: 0.8rem;
      color: var(--espresso);
      font-weight: 500;
      border-left: 3px solid var(--gold);
    }

    /* row appear animation */
    @keyframes rowSlideIn {
      from { opacity: 0; transform: translateY(8px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .resenas-table-wrap tbody tr {
      animation: rowSlideIn 0.3s ease both;
    }
    <?php
      $i = 0;
      if ($resenas) {
        $total_rows = $resenas->num_rows;
        for ($r=0; $r<$total_rows && $r<15; $r++) {
          echo ".resenas-table-wrap tbody tr:nth-child(" . ($r+1) . ") { animation-delay: " . ($r*0.04) . "s; }";
        }
        $resenas->data_seek(0);
      }
    ?>

    /* success toast */
    .toast-success {
      position: fixed;
      top: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(-80px);
      background: linear-gradient(135deg, #1f6b3a, #2e8b57);
      color: white;
      padding: 12px 24px;
      border-radius: 50px;
      font-size: 0.875rem;
      font-weight: 600;
      box-shadow: 0 8px 28px rgba(0,0,0,0.2);
      z-index: 9999;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
    }
    .toast-success.show {
      transform: translateX(-50%) translateY(0);
    }
  </style>
</head>

<body>

  <!-- HEADER (unchanged) -->
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

  <!-- SIDEBAR (unchanged) -->
  <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="menuLateral">
    <div class="offcanvas-header">
      <img src="imagenes/VinylLab.png" class="sidebar-logo">
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <nav class="nav flex-column">
        <a class="nav-link" href="https://vinyl-labs.vercel.app"><i class="bi bi-house-door me-2"></i> Inicio</a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/catalogo.php"><i class="bi bi-music-note-list me-2"></i> Catálogo</a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/add_vinilos.php"><i class="bi bi-plus-circle me-2"></i> Añadir vinilo</a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/gestionar_catalogo.php"><i class="bi bi-gear me-2"></i> Gestionar catálogo</a>
        <a class="nav-link active" href="https://vinyllabs-production.up.railway.app/gestionar_resenas.php"><i class="bi bi-chat-square-text me-2"></i> Gestionar reseñas</a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a>
      </nav>
    </div>
  </div>

  <!-- TOAST -->
  <div class="toast-success" id="toastOk">
    <i class="bi bi-check-circle-fill"></i>
    <span>Reseña eliminada correctamente</span>
  </div>

  <!-- MAIN -->
  <main class="container py-5" style="margin-top: 130px;">
    <div class="panel-card">

      <a href="https://vinyllabs-production.up.railway.app/catalogo.php"
         class="btn-back-arrow" title="Volver a gestionar catálogo">
        <i class="bi bi-arrow-left"></i>
      </a>

      <!-- HEADER TITLE -->
      <div class="text-center mb-2">
        <p class="page-subtitle mb-1" style="letter-spacing:2px; text-transform:uppercase; font-size:0.75rem;">
          Panel de administración
        </p>
        <h2 class="page-title">
          <i class="bi bi-chat-square-text me-2" style="font-size:2rem; vertical-align:middle;"></i>Gestionar Reseñas
        </h2>
        <div style="width:50px; height:3px; background:linear-gradient(90deg,var(--gold-light),var(--gold)); border-radius:3px; margin:10px auto 0;"></div>
      </div>

      <!-- STATS -->
      <div class="stats-row justify-content-center mt-4">
        <div class="stat-pill">
          <i class="bi bi-chat-dots stat-icon"></i>
          <div>
            <div class="stat-number"><?= $total_resenas ?></div>
            <div class="stat-label">Reseñas<br>en total</div>
          </div>
        </div>
        <div class="stat-pill">
          <i class="bi bi-geo-alt stat-icon"></i>
          <div>
            <div class="stat-number"><?= $total_ciudades ?></div>
            <div class="stat-label">Ciudades<br>distintas</div>
          </div>
        </div>
        <?php if (!empty($filtro_ciudad) || $filtro_vinilo > 0): ?>
        <div class="stat-pill">
          <i class="bi bi-funnel stat-icon"></i>
          <div>
            <div class="stat-number"><?= $total_con_filtro ?></div>
            <div class="stat-label">Resultados<br>filtrados</div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- FILTERS -->
      <form method="GET" action="" class="filtros-panel">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label><i class="bi bi-geo-alt me-1"></i> Ciudad</label>
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
            <label><i class="bi bi-disc me-1"></i> Vinilo</label>
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
              <i class="bi bi-x-circle"></i> Limpiar
            </a>
          </div>
        </div>
      </form>

      <?php if (!empty($filtro_ciudad) || $filtro_vinilo > 0): ?>
      <div class="filter-active-bar">
        <i class="bi bi-funnel-fill" style="color:var(--gold);"></i>
        Filtros activos:
        <?php if (!empty($filtro_ciudad)): ?>
          <strong><?= htmlspecialchars($filtro_ciudad) ?></strong>
        <?php endif; ?>
        <?php if ($filtro_vinilo > 0): ?>
          · Vinilo #<?= $filtro_vinilo ?>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- TABLE -->
      <div class="resenas-table-wrap">
        <table class="table w-100">
          <thead>
            <tr>
              <th style="width:185px;"><i class="bi bi-person me-1"></i> Cliente</th>
              <th>Comentario</th>
              <th style="width:165px;"><i class="bi bi-disc me-1"></i> Vinilo</th>
              <th style="width:90px; text-align:center;"><i class="bi bi-calendar3 me-1"></i> Fecha</th>
              <th style="width:60px; text-align:center;"></th>
            </tr>
          </thead>
          <tbody id="tablaResenas">
            <?php if ($resenas && $resenas->num_rows > 0): ?>
              <?php while ($r = $resenas->fetch_assoc()):
                $initials = strtoupper(mb_substr($r['nombre'], 0, 1));
                $ts = strtotime($r['fecha']);
              ?>
                <tr id="fila-<?= $r['id'] ?>">

                  <!-- CLIENTE -->
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar"><?= $initials ?></div>
                      <div class="user-info">
                        <div class="nombre-text"><?= htmlspecialchars($r['nombre']) ?></div>
                        <div class="badge-ciudad">
                          <i class="bi bi-geo-alt-fill" style="font-size:0.6rem;"></i>
                          <?= htmlspecialchars($r['ciudad']) ?>
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- COMENTARIO -->
                  <td>
                    <p class="comentario-cell mb-0"><?= htmlspecialchars($r['comentario']) ?></p>
                  </td>

                  <!-- VINILO -->
                  <td>
                    <span class="badge-vinilo" title="<?= htmlspecialchars($r['vinilo_nombre']) ?>">
                      💿 <?= htmlspecialchars($r['vinilo_nombre']) ?>
                    </span>
                  </td>

                  <!-- FECHA -->
                  <td>
                    <div class="fecha-wrap">
                      <div class="fecha-day"><?= date('d', $ts) ?></div>
                      <div class="fecha-month"><?= date('M Y', $ts) ?></div>
                      <div class="fecha-time"><?= date('H:i', $ts) ?></div>
                    </div>
                  </td>

                  <!-- ACCIÓN -->
                  <td class="text-center">
                    <button class="btn-eliminar"
                            onclick="eliminarResena(<?= $r['id'] ?>)"
                            title="Eliminar reseña">
                      <i class="bi bi-trash3"></i>
                    </button>
                  </td>

                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5">
                  <div class="empty-state">
                    <i class="bi bi-chat-square-x empty-icon"></i>
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

    </div><!-- /panel-card -->
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Sidebar toggle
    const offcanvasEl = document.getElementById('menuLateral');
    const btnHamb = document.getElementById('btnHamburguesa');
    offcanvasEl.addEventListener('show.bs.offcanvas', () => btnHamb.classList.add('active'));
    offcanvasEl.addEventListener('hidden.bs.offcanvas', () => btnHamb.classList.remove('active'));

    // Toast
    function showToast() {
      const t = document.getElementById('toastOk');
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 3000);
    }

    // Delete review
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
          const fila = document.getElementById('fila-' + id);
          fila.style.transition = 'opacity 0.35s ease, transform 0.35s ease, max-height 0.35s ease';
          fila.style.opacity = '0';
          fila.style.transform = 'translateX(-16px)';
          setTimeout(() => fila.remove(), 360);
          showToast();
        } else {
          alert('Error al eliminar la reseña.');
        }
      })
      .catch(() => alert('Error de conexión al eliminar la reseña.'));
    }
  </script>

</body>
</html>