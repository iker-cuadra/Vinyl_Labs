<?php
session_start();
require_once __DIR__ . '/conexion.php';

$vinilos = $conn->query("SELECT * FROM vinilos WHERE visible = 1 ORDER BY id DESC");
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
  </style>

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
<text y='0.9em' font-size='400'>💿</text>
</svg>">
</head>

<body>

  <!-- Header (igual que index) -->
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

  <!-- Menú lateral offcanvas (igual) -->
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
    <h2 class="mb-4 text-center">Catálogo de Vinilos</h2>
    <div class="row g-4">
      <?php while ($row = $vinilos->fetch_assoc()): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="card h-100 shadow-sm" style="background-color: rgba(255,255,255,0.9); border: none;">
            <?php if (!empty($row['imagen'])): ?>
              <img src="<?= htmlspecialchars($row['imagen']) ?>" class="card-img-top"
                alt="<?= htmlspecialchars($row['nombre']) ?>">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title" style="font-family: 'Bebas Neue', sans-serif;">
                <?= htmlspecialchars($row['nombre']) ?>
              </h5>
              <p class="card-text mb-3"><?= number_format($row['precio'], 2, ',', '.') ?> €</p>
              <div class="mt-auto">
                <a href="https://vinyl-labs.vercel.app/formulario.html?vinilo=<?= urlencode($row['nombre']) ?>" class="btn btn-resena w-100">
                  <i class="bi bi-star me-1"></i> Dejar reseña
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </main>

  <!-- Footer (igual que index) -->
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
          <a href="aviso-legal.html" class="footer-legal-link">Aviso Legal</a> |
          <a href="politica-privacidad.html" class="footer-legal-link">Política de Privacidad</a> |
          <a href="politica-cookies.html" class="footer-legal-link">Política de Cookies</a> |
          <a href="condiciones-uso.html" class="footer-legal-link">Condiciones de Uso</a>
        </div>
      </div>

      <!-- Banner de cookies si lo tienes -->
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

</body>

</html>