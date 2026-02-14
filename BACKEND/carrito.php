<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$carrito = $_SESSION['carrito'];
$total = 0;

// Calcular total
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Mensajes
$mensaje = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'agregado':
            $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Producto agregado al carrito
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            break;
        case 'eliminado':
            $mensaje = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-trash-fill me-2"></i> Producto eliminado del carrito
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            break;
        case 'actualizado':
            $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Carrito actualizado
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            break;
        case 'vaciado':
            $mensaje = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-cart-x-fill me-2"></i> Carrito vaciado
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Carrito de Compras — Vinyl Lab</title>

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
    .carrito-container {
      margin-top: 140px;
      margin-bottom: 60px;
      min-height: 60vh;
    }

    .carrito-header {
      background: linear-gradient(135deg, #5a2c0d 0%, #3d2714 100%);
      color: #f5deb3;
      padding: 30px;
      border-radius: 15px;
      margin-bottom: 30px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
    }

    .carrito-titulo {
      font-family: 'Bebas Neue', cursive;
      font-size: 2.5rem;
      letter-spacing: 3px;
      margin: 0;
    }

    .producto-carrito {
      background: rgba(255, 243, 230, 0.95);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 15px;
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .producto-carrito:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 25px rgba(184, 134, 11, 0.3);
    }

    .producto-imagen {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .producto-nombre {
      font-family: 'Bebas Neue', cursive;
      font-size: 1.5rem;
      color: #5a2c0d;
      margin-bottom: 5px;
    }

    .producto-precio {
      font-family: 'Raleway', sans-serif;
      font-size: 1.2rem;
      color: #b8860b;
      font-weight: 700;
    }

    .cantidad-input {
      width: 80px;
      text-align: center;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      border: 2px solid #b8860b;
      border-radius: 8px;
      padding: 8px;
    }

    .btn-eliminar {
      background: transparent;
      border: 2px solid #dc3545;
      color: #dc3545;
      padding: 8px 20px;
      border-radius: 8px;
      transition: all 0.3s ease;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
    }

    .btn-eliminar:hover {
      background: #dc3545;
      color: white;
    }

    .resumen-carrito {
      background: linear-gradient(135deg, #f5deb3 0%, #e6d4b3 100%);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
      position: sticky;
      top: 140px;
    }

    .resumen-titulo {
      font-family: 'Bebas Neue', cursive;
      font-size: 2rem;
      color: #5a2c0d;
      margin-bottom: 20px;
      letter-spacing: 2px;
    }

    .resumen-linea {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      font-family: 'Raleway', sans-serif;
      border-bottom: 1px solid rgba(90, 44, 13, 0.2);
    }

    .resumen-total {
      font-size: 1.8rem;
      font-weight: 700;
      color: #2a5a0d;
      margin-top: 20px;
    }

    .btn-finalizar {
      background: linear-gradient(135deg, #daa520 0%, #b8860b 100%);
      border: none;
      color: white;
      font-family: 'Raleway', sans-serif;
      font-weight: 700;
      font-size: 1.2rem;
      padding: 15px;
      border-radius: 10px;
      width: 100%;
      margin-top: 20px;
      transition: all 0.3s ease;
      box-shadow: 0 5px 20px rgba(184, 134, 11, 0.3);
    }

    .btn-finalizar:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(184, 134, 11, 0.5);
    }

    .btn-vaciar {
      background: transparent;
      border: 2px solid #b8860b;
      color: #b8860b;
      font-family: 'Raleway', sans-serif;
      font-weight: 600;
      padding: 10px 25px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .btn-vaciar:hover {
      background: #b8860b;
      color: white;
    }

    .carrito-vacio {
      text-align: center;
      padding: 80px 20px;
    }

    .carrito-vacio i {
      font-size: 5rem;
      color: #b8860b;
      opacity: 0.5;
      margin-bottom: 20px;
    }

    .carrito-vacio h3 {
      font-family: 'Bebas Neue', cursive;
      font-size: 2rem;
      color: #5a2c0d;
      margin-bottom: 10px;
    }

    .carrito-vacio p {
      font-family: 'Raleway', sans-serif;
      color: #7a5a3a;
      margin-bottom: 30px;
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

  <!-- Menú lateral -->
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

  <!-- Contenido del carrito -->
  <main class="container carrito-container">
    
    <div class="carrito-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="carrito-titulo">
            <i class="bi bi-cart3 me-2"></i> Mi Carrito
          </h1>
          <p class="mb-0" style="font-family: 'Raleway', sans-serif;">
            <?= count($carrito) ?> producto(s) en tu carrito
          </p>
        </div>
        <?php if (!empty($carrito)): ?>
          <a href="gestionar_carrito.php?accion=vaciar" class="btn btn-vaciar" onclick="return confirm('¿Estás seguro de vaciar el carrito?')">
            <i class="bi bi-trash me-2"></i> Vaciar carrito
          </a>
        <?php endif; ?>
      </div>
    </div>

    <?= $mensaje ?>

    <?php if (empty($carrito)): ?>
      <!-- Carrito vacío -->
      <div class="carrito-vacio">
        <i class="bi bi-cart-x"></i>
        <h3>Tu carrito está vacío</h3>
        <p>¡Descubre nuestra colección de vinilos y encuentra tus favoritos!</p>
        <a href="catalogo.php" class="btn btn-finalizar" style="max-width: 300px; margin: 0 auto;">
          <i class="bi bi-music-note-list me-2"></i> Ver catálogo
        </a>
      </div>
    <?php else: ?>
      <!-- Productos en el carrito -->
      <div class="row g-4">
        <div class="col-lg-8">
          <?php foreach ($carrito as $item): ?>
            <div class="producto-carrito">
              <div class="row align-items-center">
                <div class="col-md-2 text-center mb-3 mb-md-0">
                  <?php if (!empty($item['imagen'])): ?>
                    <img src="<?= htmlspecialchars($item['imagen']) ?>" 
                         alt="<?= htmlspecialchars($item['nombre']) ?>" 
                         class="producto-imagen"
                         onerror="this.src='https://via.placeholder.com/100x100/6c757d/ffffff?text=🎵'">
                  <?php else: ?>
                    <div class="producto-imagen d-flex align-items-center justify-content-center bg-secondary">
                      <i class="bi bi-music-note-beamed" style="font-size: 2rem; color: white;"></i>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                  <h3 class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></h3>
                  <p class="producto-precio mb-0"><?= number_format($item['precio'], 2, ',', '.') ?> €</p>
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                  <form method="POST" action="gestionar_carrito.php?accion=actualizar&id=<?= $item['id'] ?>" class="d-flex align-items-center gap-2">
                    <label class="me-2" style="font-family: 'Raleway', sans-serif; font-weight: 600; color: #5a2c0d;">Cantidad:</label>
                    <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="1" max="99" class="cantidad-input" onchange="this.form.submit()">
                  </form>
                </div>

                <div class="col-md-2 text-end mb-3 mb-md-0">
                  <p class="producto-precio mb-2">
                    <?= number_format($item['precio'] * $item['cantidad'], 2, ',', '.') ?> €
                  </p>
                </div>

                <div class="col-md-1 text-end">
                  <a href="gestionar_carrito.php?accion=eliminar&id=<?= $item['id'] ?>" 
                     class="btn btn-eliminar btn-sm"
                     onclick="return confirm('¿Eliminar este producto?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Resumen del pedido -->
        <div class="col-lg-4">
          <div class="resumen-carrito">
            <h2 class="resumen-titulo">Resumen del pedido</h2>

            <div class="resumen-linea">
              <span>Subtotal:</span>
              <span style="font-weight: 600;"><?= number_format($total, 2, ',', '.') ?> €</span>
            </div>

            <div class="resumen-linea">
              <span>Envío:</span>
              <span style="font-weight: 600; color: #2a5a0d;">GRATIS</span>
            </div>

            <div class="resumen-linea resumen-total">
              <span>Total:</span>
              <span><?= number_format($total, 2, ',', '.') ?> €</span>
            </div>

            <button class="btn btn-finalizar" onclick="alert('Función de pago en desarrollo')">
              <i class="bi bi-credit-card me-2"></i> Finalizar compra
            </button>

            <a href="catalogo.php" class="btn btn-vaciar w-100 mt-3">
              <i class="bi bi-arrow-left me-2"></i> Seguir comprando
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </main>

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