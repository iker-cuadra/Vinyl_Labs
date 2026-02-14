<?php
session_start();
require_once __DIR__ . '/conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Configuración de paginación
$vinilos_por_pagina = 8;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $vinilos_por_pagina;

// Búsqueda
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$where_busqueda = '';

if (!empty($busqueda)) {
    $where_busqueda = " AND (nombre LIKE ? OR descripcion LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
}

// Contar total de vinilos visibles con búsqueda
$count_sql = "SELECT COUNT(*) as total FROM vinilos WHERE visible = 1" . $where_busqueda;
$stmt_count = $conn->prepare($count_sql);
if (!empty($busqueda)) {
    $stmt_count->bind_param('ss', $busqueda_param, $busqueda_param);
}
$stmt_count->execute();
$total_row = $stmt_count->get_result()->fetch_assoc();
$total_vinilos = $total_row['total'];
$total_paginas = ceil($total_vinilos / $vinilos_por_pagina);

// Obtener vinilos de la página actual
$sql = "SELECT * FROM vinilos WHERE visible = 1" . $where_busqueda . " ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!empty($busqueda)) {
    $stmt->bind_param('ssii', $busqueda_param, $busqueda_param, $vinilos_por_pagina, $offset);
} else {
    $stmt->bind_param("ii", $vinilos_por_pagina, $offset);
}

$stmt->execute();
$vinilos = $stmt->get_result();

// Generar HTML de los vinilos
ob_start();
if ($vinilos && $vinilos->num_rows > 0) {
    while ($row = $vinilos->fetch_assoc()): 
?>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card h-100 shadow-sm" style="background-color: rgba(255,255,255,0.9); border: none; cursor: pointer; transition: transform 0.3s ease;" onclick="window.location.href='detalle_vinilo.php?id=<?= $row['id'] ?>';" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <?php if (!empty($row['imagen'])): 
                $ruta_imagen = htmlspecialchars($row['imagen']);
            ?>
                <img src="<?= $ruta_imagen ?>" 
                    class="card-img-top"
                    alt="<?= htmlspecialchars($row['nombre']) ?>" 
                    style="object-fit: cover; height: 300px; width: 100%;"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'card-img-top bg-secondary d-flex align-items-center justify-content-center\' style=\'height: 300px;\'><i class=\'bi bi-music-note-beamed\' style=\'font-size: 4rem; color: rgba(255,255,255,0.5);\'></i></div>';">
            <?php else: ?>
                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 300px;">
                    <i class="bi bi-music-note-beamed" style="font-size: 4rem; color: rgba(255,255,255,0.5);"></i>
                </div>
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title" style="font-family: 'Bebas Neue', sans-serif;">
                    <?= htmlspecialchars($row['nombre']) ?>
                </h5>
                <p class="card-text mb-3"><?= number_format($row['precio'], 2, ',', '.') ?> €</p>
                <div class="mt-auto d-flex flex-column gap-2">
                    <div class="d-flex gap-2">
                        <a href="detalle_vinilo.php?id=<?= $row['id'] ?>" 
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
                    <a href="gestionar_carrito.php?accion=agregar&id=<?= $row['id'] ?>" 
                        class="btn btn-add-cart btn-sm w-100"
                        onclick="event.stopPropagation();">
                        <i class="bi bi-cart-plus me-1"></i> Añadir al carrito
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php 
    endwhile;
} else {
    echo '<div class="col-12 text-center"><p class="text-muted">No hay vinilos disponibles' . (!empty($busqueda) ? ' que coincidan con tu búsqueda' : ' en este momento') . '.</p></div>';
}
$html_vinilos = ob_get_clean();

// Generar HTML de paginación
ob_start();
if ($total_paginas > 1):
?>
    <span class="pagination-info">
        Página <?= $pagina_actual ?> de <?= $total_paginas ?> 
        (<?= $total_vinilos ?> vinilo<?= $total_vinilos != 1 ? 's' : '' ?> en total)
    </span>
    
    <ul class="pagination">
        <!-- Botón anterior -->
        <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?pagina=<?= $pagina_actual - 1 ?><?= !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '' ?>" aria-label="Anterior">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        <?php
        $rango = 2;
        $inicio = max(1, $pagina_actual - $rango);
        $fin = min($total_paginas, $pagina_actual + $rango);

        // Primera página
        if ($inicio > 1) {
            echo '<li class="page-item"><a class="page-link" href="?pagina=1' . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">1</a></li>';
            if ($inicio > 2) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Páginas del rango
        for ($i = $inicio; $i <= $fin; $i++) {
            $active = $i === $pagina_actual ? 'active' : '';
            echo '<li class="page-item ' . $active . '"><a class="page-link" href="?pagina=' . $i . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">' . $i . '</a></li>';
        }

        // Última página
        if ($fin < $total_paginas) {
            if ($fin < $total_paginas - 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="?pagina=' . $total_paginas . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">' . $total_paginas . '</a></li>';
        }
        ?>

        <!-- Botón siguiente -->
        <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
            <a class="page-link" href="?pagina=<?= $pagina_actual + 1 ?><?= !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '' ?>" aria-label="Siguiente">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
<?php
endif;
$html_paginacion = ob_get_clean();

// Limpiar cualquier output previo
if (ob_get_level()) {
    ob_end_clean();
}

// Devolver respuesta JSON
header('Content-Type: application/json');
echo json_encode([
    'html' => $html_vinilos,
    'paginacion' => $html_paginacion,
    'total' => $total_vinilos,
    'total_paginas' => $total_paginas,
    'pagina_actual' => $pagina_actual
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$stmt_count->close();
$conn->close();
exit;
?>