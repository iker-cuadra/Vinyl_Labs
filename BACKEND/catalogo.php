<?php
session_start();
require_once __DIR__ . '/conexion.php';

// ── Configuración de paginación ────────────────────────────────────
$vinilos_por_pagina = 8;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $vinilos_por_pagina;

// Contar total de vinilos visibles
$total_result = $conn->query("SELECT COUNT(*) as total FROM vinilos WHERE visible = 1");
$total_row = $total_result->fetch_assoc();
$total_vinilos = $total_row['total'];
$total_paginas = ceil($total_vinilos / $vinilos_por_pagina);

// Obtener vinilos de la página actual con prepared statement
$stmt = $conn->prepare("SELECT * FROM vinilos WHERE visible = 1 ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $vinilos_por_pagina, $offset);
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
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&family=Bebas+Neue&display=swap"
    rel="stylesheet">

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

    /* ── Sección de reseñas mejorada ─────────────────────────────── */
    .resenas-section {
      background: linear-gradient(160deg, #1a0d06 0%, #3d1f10 40%, #2a1508 70%, #1f0f05 100%);
      padding: 80px 0 90px;
      position: relative;
      overflow: hidden;
    }
    
    /* Efectos de fondo decorativos */
    .resenas-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: 
        radial-gradient(circle at 15% 30%, rgba(184,134,11,0.12) 0%, transparent 50%),
        radial-gradient(circle at 85% 70%, rgba(218,165,32,0.08) 0%, transparent 60%),
        radial-gradient(circle at 50% 50%, rgba(139,105,20,0.05) 0%, transparent 70%);
      pointer-events: none;
      animation: pulseGlow 8s ease-in-out infinite;
    }
    
    .resenas-section::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, 
        transparent 0%, 
        rgba(184,134,11,0.5) 20%, 
        rgba(218,165,32,0.8) 50%, 
        rgba(184,134,11,0.5) 80%, 
        transparent 100%);
      box-shadow: 0 2px 20px rgba(184,134,11,0.3);
    }
    
    @keyframes pulseGlow {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
    
    /* Encabezados */
    .resenas-titulo {
      font-family: 'Bebas Neue', cursive;
      font-size: clamp(2.5rem, 6vw, 4rem);
      letter-spacing: 5px;
      background: linear-gradient(135deg, #f5deb3 0%, #daa520 50%, #b8860b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-align: center;
      margin-bottom: 8px;
      text-shadow: 0 4px 12px rgba(184,134,11,0.3);
      position: relative;
    }
    
    .resenas-titulo::after {
      content: '💿';
      position: absolute;
      font-size: 0.4em;
      top: -10px;
      right: -15px;
      animation: spinSlow 20s linear infinite;
      filter: drop-shadow(0 0 10px rgba(184,134,11,0.5));
    }
    
    @keyframes spinSlow {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .resenas-subtitulo {
      font-family: 'Raleway', sans-serif;
      font-size: 0.9rem;
      color: rgba(218,165,32,0.7);
      text-align: center;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      margin-bottom: 60px;
      font-weight: 300;
      position: relative;
    }
    
    .resenas-subtitulo::before,
    .resenas-subtitulo::after {
      content: '★';
      color: rgba(184,134,11,0.4);
      margin: 0 12px;
      font-size: 0.7em;
    }
    
    /* Carrusel */
    .resenas-track-wrapper { 
      position: relative; 
      overflow: hidden; 
      padding: 10px 0;
    }
    
    .resenas-track {
      display: flex;
      gap: 24px;
      transition: transform 0.6s cubic-bezier(0.25,0.46,0.45,0.94);
      will-change: transform;
    }
    
    /* Tarjetas de reseña */
    .resena-card {
      flex: 0 0 calc(33.333% - 16px);
      background: linear-gradient(145deg, rgba(255,248,235,0.08) 0%, rgba(255,248,235,0.04) 100%);
      backdrop-filter: blur(12px);
      border: 1.5px solid rgba(184,134,11,0.3);
      border-radius: 20px;
      padding: 32px 28px 28px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      box-shadow: 
        0 4px 20px rgba(0,0,0,0.3),
        inset 0 1px 0 rgba(255,255,255,0.1);
    }
    
    .resena-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, 
        transparent 0%, 
        rgba(218,165,32,0.6) 50%, 
        transparent 100%);
      border-radius: 20px 20px 0 0;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    @media (max-width: 991px) { .resena-card { flex: 0 0 calc(50% - 12px); } }
    @media (max-width: 600px)  { .resena-card { flex: 0 0 100%; } }
    
    .resena-card:hover { 
      transform: translateY(-8px) scale(1.02);
      border-color: rgba(218,165,32,0.6);
      box-shadow: 
        0 12px 40px rgba(184,134,11,0.25),
        0 0 0 1px rgba(218,165,32,0.2),
        inset 0 1px 0 rgba(255,255,255,0.15);
    }
    
    .resena-card:hover::before {
      opacity: 1;
    }
    
    /* Comillas decorativas */
    .resena-comillas {
      font-size: 4rem; 
      line-height: 0.5;
      background: linear-gradient(135deg, rgba(184,134,11,0.5), rgba(218,165,32,0.3));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-family: Georgia, serif;
      margin-bottom: 18px; 
      display: block;
      font-weight: bold;
      filter: drop-shadow(0 2px 4px rgba(184,134,11,0.2));
    }
    
    /* Comentario */
    .resena-comentario {
      font-family: 'Raleway', sans-serif; 
      font-size: 0.95rem; 
      line-height: 1.75;
      color: rgba(255,248,235,0.92); 
      margin-bottom: 24px;
      display: -webkit-box; 
      -webkit-line-clamp: 4; 
      -webkit-box-orient: vertical; 
      overflow: hidden;
      font-weight: 300;
      letter-spacing: 0.01em;
    }
    
    /* Footer de la tarjeta */
    .resena-footer {
      border-top: 1px solid rgba(184,134,11,0.25);
      padding-top: 16px; 
      display: flex; 
      align-items: center; 
      gap: 12px;
    }
    
    /* Avatar mejorado */
    .resena-avatar {
      width: 44px; 
      height: 44px; 
      border-radius: 50%;
      background: linear-gradient(135deg, #daa520 0%, #b8860b 50%, #8b6914 100%);
      display: flex; 
      align-items: center; 
      justify-content: center;
      font-family: 'Bebas Neue', cursive; 
      font-size: 1.15rem; 
      color: #fff; 
      flex-shrink: 0;
      box-shadow: 
        0 4px 12px rgba(184,134,11,0.4),
        inset 0 1px 2px rgba(255,255,255,0.3);
      border: 2px solid rgba(255,248,235,0.2);
      position: relative;
    }
    
    .resena-avatar::after {
      content: '';
      position: absolute;
      inset: -3px;
      border-radius: 50%;
      background: linear-gradient(135deg, transparent 0%, rgba(218,165,32,0.3) 100%);
      z-index: -1;
      filter: blur(4px);
    }
    
    /* Meta información */
    .resena-meta { 
      display: flex; 
      flex-direction: column; 
      gap: 3px; 
      min-width: 0; 
    }
    
    .resena-nombre {
      font-family: 'Raleway', sans-serif; 
      font-weight: 700; 
      font-size: 0.92rem;
      color: #f5deb3; 
      white-space: nowrap; 
      overflow: hidden; 
      text-overflow: ellipsis;
      letter-spacing: 0.02em;
    }
    
    .resena-ciudad { 
      font-family: 'Raleway', sans-serif; 
      font-size: 0.78rem; 
      color: rgba(218,165,32,0.6);
      font-weight: 300;
    }
    
    /* Tag del vinilo */
    .resena-vinilo-tag {
      margin-left: auto;
      background: linear-gradient(135deg, rgba(184,134,11,0.25) 0%, rgba(139,105,20,0.15) 100%);
      border: 1px solid rgba(218,165,32,0.4);
      border-radius: 999px; 
      padding: 5px 12px;
      font-family: 'Raleway', sans-serif; 
      font-size: 0.74rem; 
      font-weight: 600; 
      color: #daa520;
      white-space: nowrap; 
      overflow: hidden; 
      text-overflow: ellipsis; 
      max-width: 130px; 
      flex-shrink: 0;
      box-shadow: 0 2px 8px rgba(184,134,11,0.2);
      letter-spacing: 0.03em;
    }
    
    /* Navegación del carrusel */
    .resenas-nav {
      display: flex; 
      justify-content: center; 
      align-items: center; 
      gap: 16px; 
      margin-top: 48px;
    }
    
    .resenas-btn {
      width: 50px; 
      height: 50px; 
      border-radius: 50%;
      background: rgba(255,248,235,0.05);
      backdrop-filter: blur(8px);
      border: 2px solid rgba(184,134,11,0.5); 
      color: #daa520;
      font-size: 1.1rem; 
      display: flex; 
      align-items: center; 
      justify-content: center;
      cursor: pointer; 
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    
    .resenas-btn:hover:not(:disabled) { 
      background: rgba(184,134,11,0.3);
      border-color: #daa520;
      transform: scale(1.15);
      box-shadow: 
        0 6px 20px rgba(184,134,11,0.4),
        0 0 20px rgba(218,165,32,0.3);
    }
    
    .resenas-btn:active:not(:disabled) {
      transform: scale(1.05);
    }
    
    .resenas-btn:disabled { 
      opacity: 0.25; 
      cursor: not-allowed; 
      transform: none; 
      border-color: rgba(184,134,11,0.2);
    }
    
    /* Dots de navegación */
    .resenas-dots { 
      display: flex; 
      gap: 10px; 
      align-items: center; 
    }
    
    .resenas-dot {
      width: 10px; 
      height: 10px; 
      border-radius: 50%;
      background: rgba(184,134,11,0.35); 
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
      border: none; 
      padding: 0;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
      position: relative;
    }
    
    .resenas-dot::after {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      border: 2px solid transparent;
      transition: border-color 0.3s ease;
    }
    
    .resenas-dot:hover:not(.active) {
      background: rgba(184,134,11,0.5);
      transform: scale(1.2);
    }
    
    .resenas-dot.active { 
      background: linear-gradient(135deg, #daa520, #b8860b);
      transform: scale(1.5);
      box-shadow: 
        0 0 12px rgba(218,165,32,0.6),
        0 4px 8px rgba(0,0,0,0.4);
    }
    
    .resenas-dot.active::after {
      border-color: rgba(218,165,32,0.4);
    }
    
    /* Estado vacío */
    .resenas-empty {
      text-align: center; 
      color: rgba(245,222,179,0.5);
      font-family: 'Raleway', sans-serif; 
      font-size: 1rem; 
      padding: 48px 0;
      font-weight: 300;
      letter-spacing: 0.05em;
    }
    
    .resenas-empty i {
      opacity: 0.6;
      filter: drop-shadow(0 4px 8px rgba(184,134,11,0.3));
    }

    /* ── Paginación del catálogo ─────────────────────────────── */
    .pagination-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 50px;
      gap: 8px;
    }

    .pagination-info {
      font-family: 'Raleway', sans-serif;
      font-size: 0.9rem;
      color: #7a5a3a;
      margin-right: 20px;
      font-weight: 500;
    }

    .pagination {
      display: flex;
      gap: 6px;
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .page-item {
      display: inline-block;
    }

    .page-link {
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 42px;
      height: 42px;
      padding: 8px 14px;
      font-family: 'Raleway', sans-serif;
      font-size: 0.9rem;
      font-weight: 600;
      color: #b8860b;
      background: rgba(255, 248, 235, 0.6);
      border: 1.5px solid rgba(184, 134, 11, 0.3);
      border-radius: 8px;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .page-link:hover {
      background: rgba(184, 134, 11, 0.15);
      border-color: #b8860b;
      color: #8b6914;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(184, 134, 11, 0.3);
    }

    .page-item.active .page-link {
      background: linear-gradient(135deg, #daa520 0%, #b8860b 100%);
      border-color: #b8860b;
      color: #fff;
      box-shadow: 0 4px 15px rgba(184, 134, 11, 0.4);
      transform: scale(1.08);
      font-weight: 700;
    }

    .page-item.disabled .page-link {
      opacity: 0.4;
      cursor: not-allowed;
      pointer-events: none;
      background: rgba(255, 248, 235, 0.3);
      border-color: rgba(184, 134, 11, 0.15);
    }

    .page-link i {
      font-size: 0.85rem;
    }

    /* Estilos responsive para paginación */
    @media (max-width: 576px) {
      .pagination-wrapper {
        flex-direction: column;
        gap: 16px;
      }

      .pagination-info {
        margin-right: 0;
      }

      .page-link {
        min-width: 38px;
        height: 38px;
        padding: 6px 10px;
        font-size: 0.85rem;
      }
    }
  </style>

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
<text y='0.9em' font-size='400'>💿</text>
</svg>">
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

        <button class="btn btn-hamburguesa" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
          aria-controls="menuLateral" aria-label="Abrir menú" id="btnHamburguesa">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </div>
  </header>

  <!-- Menú lateral offcanvas -->
  <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="menuLateral" aria-labelledby="tituloMenu">
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

    <?php if ($resena_ok): ?>
      <div class="alert alert-success alert-dismissible fade show text-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        ¡Gracias! Tu reseña ha sido enviada correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
      <div class="alert alert-danger alert-dismissible fade show text-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($error_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <h2 class="mb-4 text-center">Catálogo de Vinilos</h2>
    <div class="row g-4">
      <?php 
      if ($vinilos && $vinilos->num_rows > 0) {
        while ($row = $vinilos->fetch_assoc()): 
      ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="card h-100 shadow-sm" style="background-color: rgba(255,255,255,0.9); border: none; cursor: pointer; transition: transform 0.3s ease;" onclick="window.location.href='https://vinyllabs-production.up.railway.app/detalle_vinilo.php?id=<?= $row['id'] ?>';" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <?php if (!empty($row['imagen'])): 
              // Debug: mostrar ruta
              $ruta_imagen = htmlspecialchars($row['imagen']);
              $existe_archivo = file_exists(__DIR__ . '/' . $row['imagen']) ? '✓' : '✗';
            ?>
              <!-- Debug: <?= $ruta_imagen ?> (Existe: <?= $existe_archivo ?>) -->
              <img src="<?= $ruta_imagen ?>" 
                   class="card-img-top"
                   alt="<?= htmlspecialchars($row['nombre']) ?>" 
                   style="object-fit: cover; height: 300px; width: 100%;"
                   onerror="console.error('Error cargando imagen: <?= $ruta_imagen ?>'); this.onerror=null; this.parentElement.innerHTML='<div class=\'card-img-top bg-secondary d-flex align-items-center justify-content-center\' style=\'height: 300px;\'><i class=\'bi bi-music-note-beamed\' style=\'font-size: 4rem; color: rgba(255,255,255,0.5);\'></i><div style=\'font-size:0.7rem; color:white; margin-top:10px;\'><?= $existe_archivo ?> <?= basename($ruta_imagen) ?></div></div>';">
            <?php else: ?>
              <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 300px;">
                <i class="bi bi-music-note-beamed" style="font-size: 4rem; color: rgba(255,255,255,0.5);"></i>
                <div style="font-size:0.7rem; color:white; margin-top:10px;">Sin imagen en BD</div>
              </div>
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title" style="font-family: 'Bebas Neue', sans-serif;">
                <?= htmlspecialchars($row['nombre']) ?>
              </h5>
              <p class="card-text mb-3"><?= number_format($row['precio'], 2, ',', '.') ?> €</p>
              <div class="mt-auto d-flex gap-2">
                <a href="https://vinyllabs-production.up.railway.app/detalle_vinilo.php?id=<?= $row['id'] ?>" 
                   class="btn btn-sm flex-grow-1"
                   style="background: linear-gradient(135deg, #daa520, #b8860b); color: white; font-weight: 600; border: none;"
                   onclick="event.stopPropagation();">
                  <i class="bi bi-eye me-1"></i> Ver detalles
                </a>
                <a href="https://vinyl-labs.vercel.app/formulario.html?vinilo_id=<?= (int)$row['id'] ?>&vinilo_nombre=<?= urlencode($row['nombre']) ?>"
                   class="btn btn-resena btn-sm"
                   onclick="event.stopPropagation();">
                  <i class="bi bi-star"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php 
        endwhile;
      } else {
        echo '<div class="col-12 text-center"><p class="text-muted">No hay vinilos disponibles en este momento.</p></div>';
      }
      ?>
    </div>

    <?php if ($total_paginas > 1): ?>
      <!-- Paginación -->
      <div class="pagination-wrapper">
        <span class="pagination-info">
          Página <?= $pagina_actual ?> de <?= $total_paginas ?> 
          (<?= $total_vinilos ?> vinilos en total)
        </span>
        
        <ul class="pagination">
          <!-- Botón anterior -->
          <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?pagina=<?= $pagina_actual - 1 ?><?= $resena_ok ? '&resena=ok' : '' ?>" aria-label="Anterior">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>

          <?php
          // Lógica para mostrar números de página
          $rango = 2; // Cuántas páginas mostrar a cada lado de la actual
          $inicio = max(1, $pagina_actual - $rango);
          $fin = min($total_paginas, $pagina_actual + $rango);

          // Primera página
          if ($inicio > 1) {
            echo '<li class="page-item"><a class="page-link" href="?pagina=1' . ($resena_ok ? '&resena=ok' : '') . '">1</a></li>';
            if ($inicio > 2) {
              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
          }

          // Páginas del rango
          for ($i = $inicio; $i <= $fin; $i++) {
            $active = $i === $pagina_actual ? 'active' : '';
            echo '<li class="page-item ' . $active . '"><a class="page-link" href="?pagina=' . $i . ($resena_ok ? '&resena=ok' : '') . '">' . $i . '</a></li>';
          }

          // Última página
          if ($fin < $total_paginas) {
            if ($fin < $total_paginas - 1) {
              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="?pagina=' . $total_paginas . ($resena_ok ? '&resena=ok' : '') . '">' . $total_paginas . '</a></li>';
          }
          ?>

          <!-- Botón siguiente -->
          <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
            <a class="page-link" href="?pagina=<?= $pagina_actual + 1 ?><?= $resena_ok ? '&resena=ok' : '' ?>" aria-label="Siguiente">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>
      </div>
    <?php endif; ?>
  </main>

  <!-- ── Sección de reseñas ─────────────────────────────────────────── -->
  <?php
  $resenas_arr = [];
  if ($resenas && $resenas->num_rows > 0) {
      while ($r = $resenas->fetch_assoc()) {
          $resenas_arr[] = $r;
      }
  }
  ?>
  <section class="resenas-section">
    <div class="container">
      <p class="resenas-subtitulo">Lo que dicen nuestros clientes</p>
      <h2 class="resenas-titulo">Opiniones</h2>

      <?php if (empty($resenas_arr)): ?>
        <p class="resenas-empty">
          <i class="bi bi-chat-square-dots" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
          Aún no hay reseñas. ¡Sé el primero en dejar la tuya!
        </p>
      <?php else: ?>
        <div class="resenas-track-wrapper" id="resenasWrapper">
          <div class="resenas-track" id="resenasTrack">
            <?php foreach ($resenas_arr as $r): ?>
              <div class="resena-card">
                <span class="resena-comillas">"</span>
                <p class="resena-comentario"><?= htmlspecialchars($r['comentario']) ?></p>
                <div class="resena-footer">
                  <div class="resena-avatar"><?= mb_strtoupper(mb_substr($r['nombre'], 0, 1)) ?></div>
                  <div class="resena-meta">
                    <span class="resena-nombre"><?= htmlspecialchars($r['nombre']) ?></span>
                    <span class="resena-ciudad"><i class="bi bi-geo-alt-fill me-1" style="font-size:0.65rem;"></i><?= htmlspecialchars($r['ciudad']) ?></span>
                  </div>
                  <span class="resena-vinilo-tag">💿 <?= htmlspecialchars($r['vinilo_nombre']) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="resenas-nav">
          <button class="resenas-btn" id="resenaPrev" aria-label="Anterior">
            <i class="bi bi-chevron-left"></i>
          </button>
          <div class="resenas-dots" id="resenasDots"></div>
          <button class="resenas-btn" id="resenaNext" aria-label="Siguiente">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer mt-5 pt-5 pb-4">
    <div class="container">
      <div class="row gy-4">
        <div class="col-md-3 text-center text-md-start">
          <img src="imagenes/VinylLab.png" alt="Logo Vinyl Lab" class="footer-logo mb-2">
          <p class="footer-text">El sonido del pasado, con la calidez del presente.</p>
        </div>

        <div class="col-md-3 text-center text-md-start">
          <h5 class="footer-titulo">Enlaces</h5>
          <ul class="list-unstyled footer-links">
            <li><a href="https://vinyl-labs.vercel.app">Inicio</a></li>
            <li><a href="https://vinyllabs-production.up.railway.app/catalogo.php">Catálogo</a></li>
            <li><a href="#">Ofertas</a></li>
            <li><a href="#">Contacto</a></li>
          </ul>
        </div>

        <div class="col-md-3 text-center text-md-start">
          <h5 class="footer-titulo">Síguenos</h5>
          <div class="social-icons">
            <a href="https://www.instagram.com/" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="https://www.facebook.com/" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://x.com/" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="https://www.youtube.com/" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
          </div>
        </div>

        <div class="col-md-3 text-center text-md-start">
          <h5 class="footer-titulo">Contáctanos</h5>
          <form class="footer-form">
            <input type="text" class="form-control mb-2" placeholder="Tu nombre" required>
            <input type="email" class="form-control mb-2" placeholder="Tu email" required>
            <textarea class="form-control mb-2" rows="2" placeholder="Mensaje" required></textarea>
            <button type="submit" class="btn btn-enviar w-100">Enviar</button>
          </form>
        </div>
      </div>

      <div class="footer-legal text-center mt-4 pt-3 border-top">
        <h6 class="footer-titulo mb-2">Información Legal</h6>
        <p class="footer-text small mb-2">
          Vinyl Lab cumple con la normativa vigente sobre protección de datos personales (Reglamento (UE) 2016/679 -
          RGPD, y la Ley Orgánica 3/2018 - LOPDGDD), así como con la Ley 34/2002 de Servicios de la Sociedad de la
          Información y Comercio Electrónico (LSSI-CE).
        </p>
        <div class="footer-legal-links">
          <a href="https://vinyl-labs.vercel.app/FRONTEND/legal.html" class="footer-legal-link">Aviso Legal</a> |
          <a href="https://vinyl-labs.vercel.app/FRONTEND/priv.html" class="footer-legal-link">Política de Privacidad</a> |
          <a href="https://vinyl-labs.vercel.app/FRONTEND/cookies.html" class="footer-legal-link">Política de Cookies</a> |
          <a href="https://vinyl-labs.vercel.app/FRONTEND/condiciones.html" class="footer-legal-link">Condiciones de Uso</a>
        </div>
      </div>

      <div id="cookie-banner" class="cookie-banner">
        <p>Usamos cookies propias y de terceros para analizar el tráfico y mejorar tu experiencia.
          <a href="politica-cookies.html">Más información</a>.
        </p>
        <button id="accept-cookies" class="btn btn-aceptar-cookies">Aceptar</button>
      </div>

      <div class="text-center mt-4 border-top pt-3 small footer-copy">
        &copy; 2025 Vinyl Lab — Todos los derechos reservados.
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const offcanvasEl = document.getElementById('menuLateral');
    const btnHamb = document.getElementById('btnHamburguesa');
    offcanvasEl.addEventListener('show.bs.offcanvas', () => btnHamb.classList.add('active'));
    offcanvasEl.addEventListener('hidden.bs.offcanvas', () => btnHamb.classList.remove('active'));
  </script>

  <!-- Carrusel de reseñas -->
  <script>
    (function () {
      const track   = document.getElementById('resenasTrack');
      const wrapper = document.getElementById('resenasWrapper');
      const btnPrev = document.getElementById('resenaPrev');
      const btnNext = document.getElementById('resenaNext');
      const dotsEl  = document.getElementById('resenasDots');

      if (!track) return; // sin reseñas, no hay carrusel

      let current    = 0;
      let perPage    = 3;
      const cards    = Array.from(track.querySelectorAll('.resena-card'));
      const total    = cards.length;

      function getPerPage() {
        if (window.innerWidth <= 600)  return 1;
        if (window.innerWidth <= 991)  return 2;
        return 3;
      }

      function totalPages() { return Math.ceil(total / perPage); }

      function buildDots() {
        dotsEl.innerHTML = '';
        for (let i = 0; i < totalPages(); i++) {
          const d = document.createElement('button');
          d.className = 'resenas-dot' + (i === current ? ' active' : '');
          d.setAttribute('aria-label', `Página ${i + 1}`);
          d.addEventListener('click', () => goTo(i));
          dotsEl.appendChild(d);
        }
      }

      function goTo(page) {
        const pages = totalPages();
        current = Math.max(0, Math.min(page, pages - 1));

        // Calcular offset: cuántos cards se desplazan
        const cardEl     = cards[0];
        const cardWidth  = cardEl.offsetWidth + 20; // gap 20px
        const offset     = current * perPage * cardWidth;

        track.style.transform = `translateX(-${offset}px)`;

        // Actualizar dots
        dotsEl.querySelectorAll('.resenas-dot').forEach((d, i) =>
          d.classList.toggle('active', i === current)
        );

        // Botones
        btnPrev.disabled = current === 0;
        btnNext.disabled = current >= pages - 1;
      }

      function init() {
        perPage = getPerPage();
        current = 0;
        track.style.transform = 'translateX(0)';
        buildDots();
        goTo(0);
      }

      btnPrev.addEventListener('click', () => goTo(current - 1));
      btnNext.addEventListener('click', () => goTo(current + 1));

      // Swipe táctil
      let startX = 0;
      wrapper.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
      wrapper.addEventListener('touchend',   e => {
        const diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) goTo(diff > 0 ? current + 1 : current - 1);
      });

      window.addEventListener('resize', init);
      init();
    })();
  </script>

</body>

</html>