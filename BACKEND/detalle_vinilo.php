<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito_items = $_SESSION['carrito'];

// Obtener el ID del vinilo
$vinilo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vinilo_id <= 0) {
    header('Location: catalogo.php');
    exit;
}

// Obtener información del vinilo
$stmt = $conn->prepare("SELECT * FROM vinilos WHERE id = ? AND visible = 1 LIMIT 1");
$stmt->bind_param("i", $vinilo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: catalogo.php');
    exit;
}

$vinilo = $result->fetch_assoc();

// Obtener reseñas de este vinilo
$resenas_stmt = $conn->prepare("
    SELECT nombre, ciudad, comentario, fecha 
    FROM resenas 
    WHERE vinilo_id = ? 
    ORDER BY fecha DESC
");
$resenas_stmt->bind_param("i", $vinilo_id);
$resenas_stmt->execute();
$resenas_result = $resenas_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($vinilo['nombre']) ?> — Vinyl Lab</title>

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
    .vinilo-detalle {
      margin-top: 140px;
      margin-bottom: 60px;
    }

    .imagen-vinilo {
      width: 100%;
      max-width: 500px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease;
    }

    .imagen-vinilo:hover {
      transform: scale(1.05);
    }

    .info-vinilo {
      background: rgba(255, 243, 230, 0.95);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 5px 30px rgba(0, 0, 0, 0.2);
    }

    .titulo-vinilo {
      font-family: 'Bebas Neue', cursive;
      font-size: 3rem;
      color: #5a2c0d;
      letter-spacing: 2px;
      margin-bottom: 10px;
    }

    .artista-vinilo {
      font-family: 'Raleway', sans-serif;
      font-size: 1.3rem;
      color: #b8860b;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .precio-vinilo {
      font-family: 'Bebas Neue', cursive;
      font-size: 2.5rem;
      color: #2a5a0d;
      margin: 20px 0;
    }

    .descripcion-vinilo {
      font-family: 'Raleway', sans-serif;
      font-size: 1rem;
      line-height: 1.8;
      color: #4a2010;
      margin: 25px 0;
    }

    .dato-label {
      font-family: 'Raleway', sans-serif;
      font-weight: 700;
      color: #5a2c0d;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 5px;
    }

    .dato-valor {
      font-family: 'Raleway', sans-serif;
      font-size: 1.1rem;
      color: #7a5a3a;
      margin-bottom: 20px;
    }

    .btn-resena-detalle {
      background: linear-gradient(135deg, #daa520 0%, #b8860b 100%);
      border: none;
      color: white;
      font-family: 'Raleway', sans-serif;
      font-weight: 700;
      font-size: 1.1rem;
      padding: 15px 40px;
      border-radius: 10px;
      transition: all 0.3s ease;
      box-shadow: 0 5px 20px rgba(184, 134, 11, 0.3);
    }

    .btn-resena-detalle:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(184, 134, 11, 0.5);
      background: linear-gradient(135deg, #b8860b 0%, #8b6914 100%);
    }

    .btn-volver {
      background: transparent;
      border: 2px solid #b8860b;
      color: #b8860b;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      padding: 10px 30px;
      border-radius: 10px;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-volver:hover {
      background: #b8860b;
      color: white;
    }

    /* Sección de reseñas */
    .resenas-vinilo {
      background: linear-gradient(135deg, #2a1205 0%, #4a2010 60%, #3a1a08 100%);
      padding: 60px 0;
      margin-top: 60px;
    }

    .resena-item {
      background: rgba(255, 248, 235, 0.08);
      border: 1px solid rgba(184, 134, 11, 0.3);
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }

    .resena-item:hover {
      transform: translateY(-3px);
      border-color: rgba(218, 165, 32, 0.6);
      box-shadow: 0 5px 20px rgba(184, 134, 11, 0.3);
    }

    .resena-autor {
      font-family: 'Raleway', sans-serif;
      font-weight: 700;
      color: #f5deb3;
      font-size: 1.1rem;
    }

    .resena-ciudad {
      font-family: 'Raleway', sans-serif;
      color: rgba(218, 165, 32, 0.7);
      font-size: 0.9rem;
    }

    .resena-texto {
      font-family: 'Raleway', sans-serif;
      color: rgba(255, 248, 235, 0.9);
      line-height: 1.7;
      margin-top: 15px;
    }

    .resena-fecha {
      font-family: 'Raleway', sans-serif;
      color: rgba(245, 222, 179, 0.5);
      font-size: 0.85rem;
      margin-top: 10px;
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

        <a href="https://vinyllabs-production.up.railway.app/catalogo.php" class="btn-login-custom">Catálogo</a>
        <a href="https://vinyl-labs.vercel.app" class="btn-login-custom">Inicio</a>

        <!-- Botón del carrito -->
        <a href="carrito.php" class="btn-login-custom position-relative">
          <i class="bi bi-cart3"></i> Carrito
          <?php if (!empty($carrito_items)): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= count($carrito_items) ?>
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

  <!-- Contenido del detalle -->
  <main class="container vinilo-detalle">
    
    <div class="row g-5">
      <!-- Imagen del vinilo -->
      <div class="col-lg-5 text-center">
        <?php if (!empty($vinilo['imagen'])): ?>
          <img src="<?= htmlspecialchars($vinilo['imagen']) ?>" 
               alt="<?= htmlspecialchars($vinilo['nombre']) ?>" 
               class="imagen-vinilo"
               onerror="this.src='https://via.placeholder.com/500x500/6c757d/ffffff?text=Sin+Imagen'">
        <?php else: ?>
          <img src="https://via.placeholder.com/500x500/6c757d/ffffff?text=Sin+Imagen" 
               alt="Sin imagen" 
               class="imagen-vinilo">
        <?php endif; ?>
      </div>

      <!-- Información del vinilo -->
      <div class="col-lg-7">
        <div class="info-vinilo">
          <h1 class="titulo-vinilo"><?= htmlspecialchars($vinilo['nombre']) ?></h1>
          
          <?php if (!empty($vinilo['artista'])): ?>
            <p class="artista-vinilo">
              <i class="bi bi-person-fill me-2"></i><?= htmlspecialchars($vinilo['artista']) ?>
            </p>
          <?php endif; ?>

          <div class="precio-vinilo">
            <?= number_format($vinilo['precio'], 2, ',', '.') ?> €
          </div>

          <hr style="border-color: rgba(184, 134, 11, 0.3); margin: 30px 0;">

          <?php if (!empty($vinilo['descripcion'])): ?>
            <div class="descripcion-vinilo">
              <?= nl2br(htmlspecialchars($vinilo['descripcion'])) ?>
            </div>
          <?php endif; ?>

          <div class="row mt-4">
            <?php if (!empty($vinilo['anio'])): ?>
              <div class="col-md-6">
                <p class="dato-label">
                  <i class="bi bi-calendar3 me-2"></i>Año de lanzamiento
                </p>
                <p class="dato-valor"><?= htmlspecialchars($vinilo['anio']) ?></p>
              </div>
            <?php endif; ?>

            <div class="col-md-6">
              <p class="dato-label">
                <i class="bi bi-box-seam me-2"></i>Estado
              </p>
              <p class="dato-valor">
                <span class="badge bg-success">Disponible</span>
              </p>
            </div>
          </div>

          <div class="mt-4 d-flex gap-3 flex-wrap">
            <a href="https://vinyl-labs.vercel.app/formulario.html?vinilo_id=<?= $vinilo['id'] ?>&vinilo_nombre=<?= urlencode($vinilo['nombre']) ?>" 
               class="btn btn-resena-detalle">
              <i class="bi bi-star-fill me-2"></i> Dejar una reseña
            </a>
            
            <a href="gestionar_carrito.php?accion=agregar&id=<?= $vinilo['id'] ?>" class="btn btn-volver">
              <i class="bi bi-cart-plus me-2"></i> Añadir al carrito
            </a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Sección de reseñas -->
  <?php if ($resenas_result->num_rows > 0): ?>
    <section class="resenas-vinilo">
      <div class="container">
        <h2 class="text-center mb-5" style="color: #f5deb3; font-family: 'Bebas Neue', cursive; font-size: 2.5rem; letter-spacing: 3px;">
          <i class="bi bi-chat-square-quote me-2"></i>
          Opiniones sobre este vinilo
        </h2>

        <div class="row">
          <div class="col-lg-10 mx-auto">
            <?php while ($resena = $resenas_result->fetch_assoc()): ?>
              <div class="resena-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <p class="resena-autor mb-1">
                      <i class="bi bi-person-circle me-2"></i>
                      <?= htmlspecialchars($resena['nombre']) ?>
                    </p>
                    <p class="resena-ciudad mb-0">
                      <i class="bi bi-geo-alt-fill me-1"></i>
                      <?= htmlspecialchars($resena['ciudad']) ?>
                    </p>
                  </div>
                  <p class="resena-fecha">
                    <?= date('d/m/Y', strtotime($resena['fecha'])) ?>
                  </p>
                </div>
                <p class="resena-texto mb-0">
                  "<?= htmlspecialchars($resena['comentario']) ?>"
                </p>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

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
          <a href="https://vinyl-labs.vercel.app/legal.html" class="footer-legal-link">Aviso Legal</a> |
          <a href="https://vinyl-labs.vercel.app/priv.html" class="footer-legal-link">Política de Privacidad</a> |
          <a href="https://vinyl-labs.vercel.app/cookies.html" class="footer-legal-link">Política de Cookies</a> |
          <a href="https://vinyl-labs.vercel.app/condiciones.html" class="footer-legal-link">Condiciones de Uso</a>
        </div>
      </div>

      <div id="cookie-banner" class="cookie-banner">
        <p>Usamos cookies propias y de terceros para analizar el tráfico y mejorar tu experiencia.
          <a href="https://vinyl-labs.vercel.app/cookies.html">Más información</a>.
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