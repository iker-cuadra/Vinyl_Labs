<?php
session_start();
require_once __DIR__ . '/conexion.php';

$vinilos = $conn->query("SELECT * FROM vinilos WHERE visible = 1 ORDER BY id DESC");

// Reseñas con nombre del vinilo — más recientes primero
$resenas = $conn->query("
    SELECT r.nombre, r.ciudad, r.comentario, r.fecha, v.nombre AS vinilo_nombre
    FROM resenas r
    JOIN vinilos v ON r.vinilo_id = v.id
    ORDER BY r.fecha DESC
");

// Mensaje de éxito tras enviar reseña
$resena_ok = isset($_GET['resena']) && $_GET['resena'] === 'ok';
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

    /* ── Sección de reseñas ─────────────────────────────── */
    .resenas-section {
      background: linear-gradient(135deg, #2a1205 0%, #4a2010 60%, #3a1a08 100%);
      padding: 64px 0 72px;
      position: relative;
      overflow: hidden;
    }
    .resenas-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(circle at 20% 50%, rgba(184,134,11,0.08) 0%, transparent 60%),
                        radial-gradient(circle at 80% 20%, rgba(184,134,11,0.06) 0%, transparent 50%);
      pointer-events: none;
    }
    .resenas-titulo {
      font-family: 'Bebas Neue', cursive;
      font-size: clamp(2rem, 5vw, 3rem);
      letter-spacing: 3px;
      color: #f5deb3;
      text-align: center;
      margin-bottom: 6px;
    }
    .resenas-subtitulo {
      font-family: 'Raleway', sans-serif;
      font-size: 0.85rem;
      color: rgba(245,222,179,0.5);
      text-align: center;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      margin-bottom: 48px;
    }
    .resenas-track-wrapper { position: relative; overflow: hidden; }
    .resenas-track {
      display: flex;
      gap: 20px;
      transition: transform 0.45s cubic-bezier(0.25,0.46,0.45,0.94);
      will-change: transform;
    }
    .resena-card {
      flex: 0 0 calc(33.333% - 14px);
      background: rgba(255,248,235,0.06);
      backdrop-filter: blur(6px);
      border: 1px solid rgba(184,134,11,0.25);
      border-radius: 14px;
      padding: 28px 26px 24px;
      transition: transform 0.2s ease, border-color 0.2s ease;
    }
    @media (max-width: 991px) { .resena-card { flex: 0 0 calc(50% - 10px); } }
    @media (max-width: 600px)  { .resena-card { flex: 0 0 100%; } }
    .resena-card:hover { transform: translateY(-3px); border-color: rgba(184,134,11,0.55); }
    .resena-comillas {
      font-size: 3.5rem; line-height: 0.6;
      color: rgba(184,134,11,0.35); font-family: Georgia,serif;
      margin-bottom: 14px; display: block;
    }
    .resena-comentario {
      font-family: 'Raleway', sans-serif; font-size: 0.92rem; line-height: 1.65;
      color: rgba(255,248,235,0.85); margin-bottom: 20px;
      display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;
    }
    .resena-footer {
      border-top: 1px solid rgba(184,134,11,0.2);
      padding-top: 14px; display: flex; align-items: center; gap: 10px;
    }
    .resena-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: linear-gradient(135deg,#b8860b,#8b6914);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue',cursive; font-size: 1.05rem; color: #fff; flex-shrink: 0;
    }
    .resena-meta { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .resena-nombre {
      font-family: 'Raleway',sans-serif; font-weight: 700; font-size: 0.88rem;
      color: #f5deb3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .resena-ciudad { font-family: 'Raleway',sans-serif; font-size: 0.75rem; color: rgba(245,222,179,0.5); }
    .resena-vinilo-tag {
      margin-left: auto;
      background: rgba(184,134,11,0.18); border: 1px solid rgba(184,134,11,0.3);
      border-radius: 999px; padding: 3px 10px;
      font-family: 'Raleway',sans-serif; font-size: 0.72rem; font-weight: 600; color: #d4a830;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; flex-shrink: 0;
    }
    .resenas-nav {
      display: flex; justify-content: center; align-items: center; gap: 12px; margin-top: 36px;
    }
    .resenas-btn {
      width: 42px; height: 42px; border-radius: 50%;
      background: transparent; border: 1.5px solid rgba(184,134,11,0.4); color: #d4a830;
      font-size: 1rem; display: flex; align-items: center; justify-content: center;
      cursor: pointer; transition: background 0.2s, border-color 0.2s, transform 0.15s;
    }
    .resenas-btn:hover { background: rgba(184,134,11,0.2); border-color: #b8860b; transform: scale(1.08); }
    .resenas-btn:disabled { opacity: 0.3; cursor: not-allowed; transform: none; }
    .resenas-dots { display: flex; gap: 7px; align-items: center; }
    .resenas-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: rgba(184,134,11,0.3); cursor: pointer;
      transition: background 0.2s, transform 0.2s; border: none; padding: 0;
    }
    .resenas-dot.active { background: #b8860b; transform: scale(1.35); }
    .resenas-empty {
      text-align: center; color: rgba(245,222,179,0.45);
      font-family: 'Raleway',sans-serif; font-size: 0.95rem; padding: 32px 0;
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
                <a href="https://vinyl-labs.vercel.app/formulario.html?vinilo_id=<?= (int)$row['id'] ?>&vinilo_nombre=<?= urlencode($row['nombre']) ?>"
                   class="btn btn-resena w-100">
                  <i class="bi bi-star me-1"></i> Dejar reseña
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
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
          <a href="aviso-legal.html" class="footer-legal-link">Aviso Legal</a> |
          <a href="politica-privacidad.html" class="footer-legal-link">Política de Privacidad</a> |
          <a href="politica-cookies.html" class="footer-legal-link">Política de Cookies</a> |
          <a href="condiciones-uso.html" class="footer-legal-link">Condiciones de Uso</a>
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