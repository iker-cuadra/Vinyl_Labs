<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito = $_SESSION['carrito'];

// Obtener reseñas para la sección de opiniones
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

    /* Botones de administración dentro del catálogo */
    .admin-buttons {
      position: absolute;
      top: 20px;
      right: 20px;
      display: flex;
      gap: 10px;
      z-index: 10;
    }

    .btn-admin {
      background: linear-gradient(135deg, #daa520, #b8860b);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      font-size: 0.9rem;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(184, 134, 11, 0.3);
      white-space: nowrap;
    }

    .btn-admin:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(184, 134, 11, 0.5);
      color: white;
    }

    .btn-admin i {
      margin-right: 5px;
    }

    /* Responsive para móvil */
    @media (max-width: 768px) {
      .admin-buttons {
        position: static;
        flex-direction: column;
        margin-bottom: 20px;
      }
      
      .btn-admin {
        width: 100%;
        text-align: center;
      }
    }

    /* ── Barra de búsqueda ─────────────────────────────── */
    .search-container {
      position: relative;
      max-width: 600px;
      margin: 0 auto 40px;
    }

    .search-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .search-input {
      width: 100%;
      padding: 14px 50px 14px 20px;
      font-family: 'Raleway', sans-serif;
      font-size: 1rem;
      border: 2px solid rgba(184, 134, 11, 0.3);
      border-radius: 50px;
      background: rgba(255, 248, 235, 0.9);
      color: #5a4a3a;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .search-input:focus {
      outline: none;
      border-color: #b8860b;
      background: #fff;
      box-shadow: 0 6px 25px rgba(184, 134, 11, 0.25);
    }

    .search-input::placeholder {
      color: rgba(90, 74, 58, 0.5);
    }

    .search-icon {
      position: absolute;
      right: 20px;
      color: #b8860b;
      font-size: 1.2rem;
      pointer-events: none;
    }

    .clear-search {
      position: absolute;
      right: 55px;
      background: none;
      border: none;
      color: #b8860b;
      font-size: 1.2rem;
      cursor: pointer;
      padding: 5px;
      display: none;
      transition: color 0.2s ease;
    }

    .clear-search:hover {
      color: #8b6914;
    }

    .clear-search.show {
      display: block;
    }

    .search-results-info {
      text-align: center;
      margin: 20px 0;
      font-family: 'Raleway', sans-serif;
      font-size: 0.95rem;
      color: #7a5a3a;
      font-weight: 500;
    }

    .search-results-info .highlight {
      color: #b8860b;
      font-weight: 700;
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

    .btn-add-cart:active {
      transform: translateY(0);
    }

    .btn-add-cart i {
      transition: transform 0.3s ease;
    }

    .btn-add-cart:hover i {
      transform: scale(1.2);
    }

    /* ── Sección de reseñas mejorada ─────────────────────────────── */
    .resenas-section {
      background: linear-gradient(160deg, #1a0d06 0%, #3d1f10 40%, #2a1508 70%, #1f0f05 100%);
      padding: 80px 0 90px;
      position: relative;
      overflow: hidden;
    }
    
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
    
    .resena-footer {
      border-top: 1px solid rgba(184,134,11,0.25);
      padding-top: 16px; 
      display: flex; 
      align-items: center; 
      gap: 12px;
    }
    
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

        <button class="btn btn-hamburguesa" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
          aria-controls="menuLateral" aria-label="Abrir menú" id="btnHamburguesa">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </div>
  </header>

  <!-- Menú lateral offcanvas -->
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
        <?php if (isset($_SESSION['usuario'])): ?>
          <a class="nav-link" href="https://vinyllabs-production.up.railway.app/add_vinilos.php">
            <i class="bi bi-plus-circle me-2"></i> Añadir vinilo
          </a>
          <a class="nav-link" href="https://vinyllabs-production.up.railway.app/gestionar_catalogo.php">
            <i class="bi bi-gear me-2"></i> Gestionar catálogo
          </a>
          <a class="nav-link" href="https://vinyllabs-production.up.railway.app/logout.php">
            <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
          </a>
        <?php endif; ?>
        <?php if (!isset($_SESSION['usuario'])): ?>
          <a class="nav-link" href="https://vinyl-labs.vercel.app/login.html">
            <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar sesión
          </a>
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

    <?php if ($carrito_msg): ?>
      <div class="alert alert-success alert-dismissible fade show text-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        ¡Vinilo añadido al carrito correctamente!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <!-- Botones de administración (solo si hay sesión) -->
    <?php if (isset($_SESSION['usuario'])): ?>
      <div class="admin-buttons">
        <a href="https://vinyllabs-production.up.railway.app/add_vinilos.php" class="btn-admin">
          <i class="bi bi-plus-circle"></i> Añadir vinilo
        </a>
        <a href="https://vinyllabs-production.up.railway.app/gestionar_catalogo.php" class="btn-admin">
          <i class="bi bi-gear"></i> Gestionar catálogo
        </a>
        <a href="https://vinyllabs-production.up.railway.app/gestionar_resenas.php" class="btn-admin">
          <i class="bi bi-chat-square-text"></i> Reseñas
        </a>
      </div>
    <?php endif; ?>

    <h2 class="mb-4 text-center">Catálogo de Vinilos</h2>

    <!-- Barra de búsqueda -->
    <div class="search-container">
      <div class="search-wrapper">
        <input 
          type="text" 
          id="searchInput" 
          class="search-input" 
          placeholder="Buscar por nombre o descripción..."
          autocomplete="off"
        >
        <button type="button" class="clear-search" id="clearSearch" title="Limpiar búsqueda">
          <i class="bi bi-x-circle-fill"></i>
        </button>
        <i class="bi bi-search search-icon"></i>
      </div>
    </div>

    <div class="row g-4" id="catalogoGrid">
      <!-- Los vinilos se cargarán aquí dinámicamente con AJAX -->
      <div class="col-12 text-center py-5">
        <div class="spinner-border text-warning" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <div class="pagination-wrapper" style="display: none;">
      <!-- Se llenará dinámicamente con AJAX -->
    </div>
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
            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
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
          <a href="https://vinyl-labs.vercel.app/Avisos/legal.html" class="footer-legal-link">Aviso Legal</a> |
          <a href="https://vinyl-labs.vercel.app/Avisos/priv.html" class="footer-legal-link">Política de Privacidad</a> |
          <a href="https://vinyl-labs.vercel.app/Avisos/cookies.html" class="footer-legal-link">Política de Cookies</a> |
          <a href="https://vinyl-labs.vercel.app/Avisos/condiciones.html" class="footer-legal-link">Condiciones de Uso</a>
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

  <!-- Script de búsqueda en tiempo real con AJAX -->
  <script>
    (function() {
      const searchInput = document.getElementById('searchInput');
      const clearBtn = document.getElementById('clearSearch');
      const catalogoGrid = document.getElementById('catalogoGrid');
      const paginationWrapper = document.querySelector('.pagination-wrapper');
      let searchTimeout;
      let currentPage = 1;

      // Mostrar/ocultar botón de limpiar
      function updateClearButton() {
        if (searchInput.value.trim().length > 0) {
          clearBtn.classList.add('show');
        } else {
          clearBtn.classList.remove('show');
        }
      }

      // Realizar búsqueda con AJAX
      function performSearch(page = 1) {
        const searchTerm = searchInput.value.trim();
        currentPage = page;

        // Mostrar indicador de carga
        catalogoGrid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Cargando...</span></div></div>';

        // Hacer petición AJAX
        const url = `buscar_vinilos.php?buscar=${encodeURIComponent(searchTerm)}&pagina=${page}`;
        
        fetch(url)
          .then(response => response.json())
          .then(data => {
            // Actualizar grid de productos
            catalogoGrid.innerHTML = data.html;

            // Actualizar información de resultados
            let infoElement = document.querySelector('.search-results-info');
            
            if (searchTerm.length > 0 && data.total > 0) {
              // Si no existe el elemento, crearlo
              if (!infoElement) {
                infoElement = document.createElement('div');
                infoElement.className = 'search-results-info';
                searchInput.closest('.search-container').insertAdjacentElement('afterend', infoElement);
              }
              infoElement.innerHTML = `Se encontraron <span class="highlight">${data.total}</span> resultado${data.total != 1 ? 's' : ''} para "<span class="highlight">${searchTerm}</span>"`;
              infoElement.style.display = 'block';
            } else {
              // Ocultar o eliminar el mensaje si no hay búsqueda o no hay resultados
              if (infoElement) {
                infoElement.style.display = 'none';
              }
            }

            // Actualizar paginación
            if (data.total_paginas > 1) {
              paginationWrapper.innerHTML = data.paginacion;
              paginationWrapper.style.display = 'flex';
              
              // Agregar event listeners a los botones de paginación
              paginationWrapper.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                  e.preventDefault();
                  const pageUrl = new URL(this.href);
                  const pageNum = pageUrl.searchParams.get('pagina');
                  if (pageNum) {
                    performSearch(parseInt(pageNum));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                  }
                });
              });
            } else {
              paginationWrapper.style.display = 'none';
            }

            // Actualizar URL sin recargar
            const newUrl = searchTerm.length > 0 
              ? `?buscar=${encodeURIComponent(searchTerm)}&pagina=${page}`
              : `?pagina=${page}`;
            window.history.pushState({}, '', newUrl);
          })
          .catch(error => {
            console.error('Error en la búsqueda:', error);
            catalogoGrid.innerHTML = '<div class="col-12 text-center"><p class="text-danger">Error al cargar los resultados. Por favor, intenta de nuevo.</p></div>';
          });
      }

      // Event listener para el input (búsqueda mientras escribes)
      searchInput.addEventListener('input', function() {
        updateClearButton();
        
        // Debounce: esperar 300ms después de que el usuario deje de escribir
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => performSearch(1), 300);
      });

   
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          clearTimeout(searchTimeout);
          performSearch(1);
        }
      });

      // Event listener para el botón de limpiar
      clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        updateClearButton();
        performSearch(1);
      });

      // Inicializar - cargar vinilos al inicio
      updateClearButton();
      
      // Obtener parámetros de URL
      const urlParams = new URLSearchParams(window.location.search);
      const initialSearch = urlParams.get('buscar') || '';
      const initialPage = parseInt(urlParams.get('pagina')) || 1;
      
      if (initialSearch) {
        searchInput.value = initialSearch;
        updateClearButton();
      }
      
      performSearch(initialPage);
    })();
  </script>

  <!-- Carrusel de reseñas -->
  <script>
    (function () {
      const track   = document.getElementById('resenasTrack');
      const wrapper = document.getElementById('resenasWrapper');
      const btnPrev = document.getElementById('resenaPrev');
      const btnNext = document.getElementById('resenaNext');
      const dotsEl  = document.getElementById('resenasDots');

      if (!track) return;

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

        const cardEl     = cards[0];
        const cardWidth  = cardEl.offsetWidth + 24;
        const offset     = current * perPage * cardWidth;

        track.style.transform = `translateX(-${offset}px)`;

        dotsEl.querySelectorAll('.resenas-dot').forEach((d, i) =>
          d.classList.toggle('active', i === current)
        );

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
