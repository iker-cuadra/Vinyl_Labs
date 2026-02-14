<?php
session_start();
require_once __DIR__ . '/conexion.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Gestionar Catálogo - Vinyl Lab</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&family=Bebas+Neue&display=swap"
    rel="stylesheet">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Estilos -->
  <link rel="stylesheet" href="styles.css">

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
<text y='0.9em' font-size='400'>💿</text>
</svg>">
</head>

<body style="
  background-image: url('https://www.toptal.com/designers/subtlepatterns/uploads/wood_pattern.png');
  background-attachment: fixed;
">

  <!-- HEADER -->
  <header class="main-header">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="header-left d-flex align-items-center">
        <img src="imagenes/VinylLab.png" class="header-logo me-2">
        <h1 class="header-title">Vinyl Lab</h1>
      </div>

      <div class="d-flex align-items-center gap-2">
        <a href="https://vinyllabs-production.up.railway.app/add_vinilos.php" class="btn-login-custom">Añadir vinilo</a>
        <a href="https://vinyl-labs.vercel.app" class="btn-login-custom">Inicio</a>
        <a href="https://vinyllabs-production.up.railway.app/logout.php" class="btn-login-custom">Cerrar sesión</a>

        <button class="btn btn-hamburguesa" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
          id="btnHamburguesa">
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
  <main class="container py-5" style="margin-top:130px;">
    <div class="card shadow-lg mx-auto p-4"
      style="max-width:1200px; background-color:rgba(255,243,230,0.97); border-radius:16px;">

      <h2 class="text-center mb-4" style="font-family:'Bebas Neue'; color:#5a2c0d;">
        Gestión del catálogo
      </h2>

      <!-- BUSCADOR EN TIEMPO REAL -->
      <input type="text" id="buscar" class="form-control form-control-lg mb-4" placeholder="Buscar vinilo por nombre..."
        autocomplete="off">

      <!-- TABLA -->
      <div class="table-responsive">
        <table class="table align-middle text-center">
          <thead style="background-color:#3d2714; color:white;">
            <tr>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Precio</th>
              <th>Visible</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="resultado">
            <!-- Resultados AJAX -->
          </tbody>
        </table>
      </div>

    </div>
  </main>

  <!-- Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- BÚSQUEDA EN TIEMPO REAL -->
  <script>
    const input = document.getElementById('buscar');
    const resultado = document.getElementById('resultado');

    function buscarVinilos(valor = '') {
      fetch('buscar_vinilos_admin.php?buscar=' + encodeURIComponent(valor))
        .then(res => res.text())
        .then(data => {
          resultado.innerHTML = data;
        })
        .catch(error => {
          console.error('Error:', error);
          resultado.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos</td></tr>';
        });
    }

    // Carga inicial
    buscarVinilos();

    // Evento en tiempo real
    input.addEventListener('keyup', () => {
      buscarVinilos(input.value);
    });

    // Función para cambiar visibilidad
    function toggleVisible(id, visible) {
      const estado = visible ? 1 : 0;
      fetch('toggle_vinilo.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id + '&visible=' + estado
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Mostrar mensaje de éxito
          const mensaje = document.createElement('div');
          mensaje.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
          mensaje.style.zIndex = '9999';
          mensaje.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>
            Vinilo ${visible ? 'visible' : 'oculto'} correctamente
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          `;
          document.body.appendChild(mensaje);
          setTimeout(() => mensaje.remove(), 3000);
        } else {
          alert('Error al actualizar: ' + (data.message || 'Desconocido'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la visibilidad');
      });
    }

    // Función para eliminar vinilo
    function eliminarVinilo(id) {
      if (!confirm('¿Estás seguro de que quieres eliminar este vinilo?')) {
        return;
      }

      fetch('eliminar_vinilo.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Recargar la búsqueda
          buscarVinilos(input.value);
          
          // Mostrar mensaje de éxito
          const mensaje = document.createElement('div');
          mensaje.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
          mensaje.style.zIndex = '9999';
          mensaje.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>
            Vinilo eliminado correctamente
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          `;
          document.body.appendChild(mensaje);
          setTimeout(() => mensaje.remove(), 3000);
        } else {
          alert('Error al eliminar: ' + (data.message || 'Desconocido'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el vinilo');
      });
    }
  </script>

</body>

</html>