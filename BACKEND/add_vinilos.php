<?php
session_start();

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Gestionar Vinilos - Vinyl Lab</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&family=Bebas+Neue&display=swap"
    rel="stylesheet">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Estilos -->
  <link rel="stylesheet" href="styles.css">

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
<text y='0.9em' font-size='400'>💿</text>
</svg>">

  <style>
    /* Estilo para la flecha de regreso */
    .btn-back-arrow {
      position: absolute;
      top: 20px;
      left: 20px;
      background: linear-gradient(135deg, #5a2c0d, #3d2714);
      color: #f5deb3;
      border: 2px solid rgba(184, 134, 11, 0.3);
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      font-size: 1.5rem;
      box-shadow: 0 4px 15px rgba(90, 44, 13, 0.4);
      transition: all 0.3s ease;
      z-index: 10;
    }

    .btn-back-arrow:hover {
      transform: translateY(-3px) scale(1.08);
      box-shadow: 0 6px 25px rgba(90, 44, 13, 0.6);
      background: linear-gradient(135deg, #6d3610, #4a2e18);
      color: #daa520;
      border-color: rgba(218, 165, 32, 0.5);
    }

    .btn-back-arrow:active {
      transform: translateY(-1px) scale(1.03);
    }
  </style>
</head>

<body>

  <!-- HEADER -->
  <header class="main-header">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="header-left d-flex align-items-center">
        <img src="imagenes/VinylLab.png" alt="Logo Vinyl Lab" class="header-logo me-2">
        <h1 class="header-title">Vinyl Lab</h1>
      </div>

      <div class="d-flex align-items-center gap-2">
        <a href="https://vinyl-labs.vercel.app" class="btn-login-custom">Inicio</a>
        <a href="https://vinyllabs-production.up.railway.app/logout.php" class="btn-login-custom">Cerrar sesión</a>

        <button class="btn btn-hamburguesa" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
          aria-controls="menuLateral" aria-label="Abrir menú" id="btnHamburguesa">
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
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/logout.php">
          <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
        </a>
      </nav>
    </div>
  </div>

  <!-- CONTENIDO -->
  <main class="container py-5" style="margin-top: 130px;">
    <div class="card shadow-lg mx-auto p-4" style="max-width: 850px; border-radius: 14px; background-color: #fff3e6; position: relative;">

      <!-- Flecha de regreso al catálogo -->
      <a href="https://vinyllabs-production.up.railway.app/catalogo.php?pagina=1" 
         class="btn-back-arrow"
         title="Volver al catálogo">
        <i class="bi bi-arrow-left"></i>
      </a>

      <h2 class="mb-4 text-center" style="font-family: 'Bebas Neue', cursive; color: #5a2c0d;">
        Añadir nuevo vinilo
      </h2>

      <form action="https://vinyllabs-production.up.railway.app/guardar_vinilo.php" method="POST"
        enctype="multipart/form-data">

        <div class="mb-3">
          <label class="form-label">Nombre del vinilo</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Nombre del artista</label>
          <input type="text" name="artista" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3" required></textarea>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Precio (€)</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Año</label>
            <input type="number" name="anio" class="form-control" required>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Imagen del vinilo</label>
          <input type="file" name="imagen" class="form-control" accept="image/*" required>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-catalogo px-5">
            Guardar vinilo
          </button>
        </div>

      </form>
    </div>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const offcanvasEl = document.getElementById('menuLateral');
    const btnHamb = document.getElementById('btnHamburguesa');
    offcanvasEl.addEventListener('show.bs.offcanvas', () => btnHamb.classList.add('active'));
    offcanvasEl.addEventListener('hidden.bs.offcanvas', () => btnHamb.classList.remove('active'));
  </script>

</body>

</html>