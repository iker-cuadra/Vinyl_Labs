<?php
session_start();
require_once __DIR__ . '/conexion.php';

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
        <a href="https://vinyllabs-production.up.railway.app/catalogo.php" class="btn-login-custom">Catálogo</a>
        <a href="https://vinyl-labs.vercel.app" class="btn-login-custom d-none d-md-inline-block">Inicio</a>

        <button class="btn btn-hamburguesa" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
          aria-controls="menuLateral" aria-label="Abrir menú">
          <i class="bi bi-list" style="font-size: 1.5rem;"></i>
        </button>
      </div>
    </div>
  </header>

  <!-- Menú lateral -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="menuLateral" aria-labelledby="menuLateralLabel" style="background: linear-gradient(180deg, #3d2714 0%, #2a1a0d 100%); width: 280px;">
    <div class="offcanvas-header" style="border-bottom: 1px solid rgba(184, 134, 11, 0.3); padding: 20px;">
      <div class="d-flex align-items-center">
        <img src="imagenes/VinylLab.png" alt="Logo Vinyl Lab" style="height: 40px; margin-right: 10px;">
        <h5 class="offcanvas-title" id="menuLateralLabel" style="color: #f5deb3; font-family: 'Bebas Neue', cursive; font-size: 1.5rem; margin: 0;">Vinyl Lab</h5>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
    </div>
    <div class="offcanvas-body" style="padding: 30px 20px;">
      <nav class="nav flex-column gap-3">
        <a class="nav-link" href="https://vinyl-labs.vercel.app" style="color: #f5deb3; font-family: 'Raleway', sans-serif; font-size: 1.1rem; padding: 12px 15px; border-radius: 8px; transition: all 0.3s; text-decoration: none;">
          <i class="bi bi-house-door me-2"></i> Inicio
        </a>
        <a class="nav-link" href="https://vinyllabs-production.up.railway.app/catalogo.php" style="color: #f5deb3; font-family: 'Raleway', sans-serif; font-size: 1.1rem; padding: 12px 15px; border-radius: 8px; transition: all 0.3s; text-decoration: none; background: rgba(184, 134, 11, 0.2);">
          <i class="bi bi-music-note-list me-2"></i> Catálogo
        </a>
        <a class="nav-link" href="#" style="color: #f5deb3; font-family: 'Raleway', sans-serif; font-size: 1.1rem; padding: 12px 15px; border-radius: 8px; transition: all 0.3s; text-decoration: none;">
          <i class="bi bi-tag me-2"></i> Ofertas
        </a>
        <a class="nav-link" href="#" style="color: #f5deb3; font-family: 'Raleway', sans-serif; font-size: 1.1rem; padding: 12px 15px; border-radius: 8px; transition: all 0.3s; text-decoration: none;">
          <i class="bi bi-envelope me-2"></i> Contacto
        </a>
      </nav>
    </div>
  </div>

  <!-- Contenido del detalle -->
  <main class="container vinilo-detalle">
    
    <div class="mb-4">
      <a href="catalogo.php" class="btn btn-volver">
        <i class="bi bi-arrow-left me-2"></i> Volver al catálogo
      </a>
    </div>

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
            
            <button class="btn btn-volver">
              <i class="bi bi-cart-plus me-2"></i> Añadir al carrito
            </button>
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
      <div class="text-center">
        <img src="imagenes/VinylLab.png" alt="Logo Vinyl Lab" class="footer-logo mb-3" style="height: 60px;">
        <p class="footer-text">El sonido del pasado, con la calidez del presente.</p>
        <div class="text-center mt-4 small footer-copy">
          &copy; 2025 Vinyl Lab — Todos los derechos reservados.
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
