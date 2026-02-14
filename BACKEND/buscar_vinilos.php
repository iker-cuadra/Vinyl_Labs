<?php
// Limpiar cualquier output previo
while (ob_get_level()) ob_end_clean();

session_start();
require_once __DIR__ . '/conexion.php';

try {
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

    // Contar total
    $count_sql = "SELECT COUNT(*) as total FROM vinilos WHERE visible = 1" . $where_busqueda;
    $stmt_count = $conn->prepare($count_sql);
    if (!empty($busqueda)) {
        $stmt_count->bind_param('ss', $busqueda_param, $busqueda_param);
    }
    $stmt_count->execute();
    $total_row = $stmt_count->get_result()->fetch_assoc();
    $total_vinilos = $total_row['total'];
    $total_paginas = ceil($total_vinilos / $vinilos_por_pagina);

    // Obtener vinilos
    $sql = "SELECT * FROM vinilos WHERE visible = 1" . $where_busqueda . " ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    if (!empty($busqueda)) {
        $stmt->bind_param('ssii', $busqueda_param, $busqueda_param, $vinilos_por_pagina, $offset);
    } else {
        $stmt->bind_param("ii", $vinilos_por_pagina, $offset);
    }

    $stmt->execute();
    $vinilos = $stmt->get_result();

    // Construir HTML
    $html_vinilos = '';

    if ($vinilos && $vinilos->num_rows > 0) {
        while ($row = $vinilos->fetch_assoc()) {
            $id = (int)$row['id'];
            $nombre = htmlspecialchars($row['nombre'], ENT_QUOTES);
            $precio = number_format($row['precio'], 2, ',', '.');
            $imagen = !empty($row['imagen']) ? htmlspecialchars($row['imagen'], ENT_QUOTES) : '';
            
            $html_vinilos .= '<div class="col-12 col-sm-6 col-md-4 col-lg-3">';
            $html_vinilos .= '<div class="card h-100 shadow-sm" style="background-color: rgba(255,255,255,0.9); border: none; cursor: pointer; transition: transform 0.3s ease;" onclick="window.location.href=\'detalle_vinilo.php?id=' . $id . '\';" onmouseover="this.style.transform=\'translateY(-5px)\'" onmouseout="this.style.transform=\'translateY(0)\'">';
            
            if (!empty($imagen)) {
                $html_vinilos .= '<img src="' . $imagen . '" class="card-img-top" alt="' . $nombre . '" style="object-fit: cover; height: 300px; width: 100%;">';
            } else {
                $html_vinilos .= '<div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 300px;"><i class="bi bi-music-note-beamed" style="font-size: 4rem; color: rgba(255,255,255,0.5);"></i></div>';
            }
            
            $html_vinilos .= '<div class="card-body d-flex flex-column">';
            $html_vinilos .= '<h5 class="card-title" style="font-family: \'Bebas Neue\', sans-serif;">' . $nombre . '</h5>';
            $html_vinilos .= '<p class="card-text mb-3">' . $precio . ' €</p>';
            $html_vinilos .= '<div class="mt-auto d-flex flex-column gap-2">';
            $html_vinilos .= '<div class="d-flex gap-2">';
            $html_vinilos .= '<a href="detalle_vinilo.php?id=' . $id . '" class="btn btn-sm flex-grow-1" style="background: linear-gradient(135deg, #daa520, #b8860b); color: white; font-weight: 600; border: none;" onclick="event.stopPropagation();"><i class="bi bi-eye me-1"></i> Ver detalles</a>';
            $html_vinilos .= '<a href="https://vinyl-labs.vercel.app/formulario.html?vinilo_id=' . $id . '&vinilo_nombre=' . urlencode($row['nombre']) . '" class="btn btn-resena btn-sm" onclick="event.stopPropagation();"><i class="bi bi-star"></i></a>';
            $html_vinilos .= '</div>';
            $html_vinilos .= '<a href="gestionar_carrito.php?accion=agregar&id=' . $id . '" class="btn btn-add-cart btn-sm w-100" onclick="event.stopPropagation();"><i class="bi bi-cart-plus me-1"></i> Añadir al carrito</a>';
            $html_vinilos .= '</div></div></div></div>';
        }
    } else {
        $html_vinilos = '<div class="col-12 text-center"><p class="text-muted">No hay vinilos disponibles' . (!empty($busqueda) ? ' que coincidan con tu búsqueda' : ' en este momento') . '.</p></div>';
    }

    // Construir paginación
    $html_paginacion = '';
    
    if ($total_paginas > 1) {
        $html_paginacion .= '<span class="pagination-info">Página ' . $pagina_actual . ' de ' . $total_paginas . ' (' . $total_vinilos . ' vinilo' . ($total_vinilos != 1 ? 's' : '') . ' en total)</span>';
        $html_paginacion .= '<ul class="pagination">';
        
        // Botón anterior
        $disabled = $pagina_actual <= 1 ? 'disabled' : '';
        $html_paginacion .= '<li class="page-item ' . $disabled . '"><a class="page-link" href="?pagina=' . ($pagina_actual - 1) . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '"><i class="bi bi-chevron-left"></i></a></li>';
        
        $rango = 2;
        $inicio = max(1, $pagina_actual - $rango);
        $fin = min($total_paginas, $pagina_actual + $rango);

        if ($inicio > 1) {
            $html_paginacion .= '<li class="page-item"><a class="page-link" href="?pagina=1' . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">1</a></li>';
            if ($inicio > 2) $html_paginacion .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for ($i = $inicio; $i <= $fin; $i++) {
            $active = $i === $pagina_actual ? 'active' : '';
            $html_paginacion .= '<li class="page-item ' . $active . '"><a class="page-link" href="?pagina=' . $i . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">' . $i . '</a></li>';
        }

        if ($fin < $total_paginas) {
            if ($fin < $total_paginas - 1) $html_paginacion .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            $html_paginacion .= '<li class="page-item"><a class="page-link" href="?pagina=' . $total_paginas . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">' . $total_paginas . '</a></li>';
        }

        $disabled = $pagina_actual >= $total_paginas ? 'disabled' : '';
        $html_paginacion .= '<li class="page-item ' . $disabled . '"><a class="page-link" href="?pagina=' . ($pagina_actual + 1) . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '"><i class="bi bi-chevron-right"></i></a></li>';
        $html_paginacion .= '</ul>';
    }

    $stmt->close();
    $stmt_count->close();
    $conn->close();

    // Enviar JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'html' => $html_vinilos,
        'paginacion' => $html_paginacion,
        'total' => $total_vinilos,
        'total_paginas' => $total_paginas,
        'pagina_actual' => $pagina_actual
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'html' => '<div class="col-12 text-center"><p class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</p></div>',
        'paginacion' => '',
        'total' => 0,
        'total_paginas' => 0,
        'pagina_actual' => 1
    ]);
}
exit;
?><?php
// Limpiar cualquier output previo
while (ob_get_level()) ob_end_clean();

session_start();
require_once __DIR__ . '/conexion.php';

try {
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

    // Contar total
    $count_sql = "SELECT COUNT(*) as total FROM vinilos WHERE visible = 1" . $where_busqueda;
    $stmt_count = $conn->prepare($count_sql);
    if (!empty($busqueda)) {
        $stmt_count->bind_param('ss', $busqueda_param, $busqueda_param);
    }
    $stmt_count->execute();
    $total_row = $stmt_count->get_result()->fetch_assoc();
    $total_vinilos = $total_row['total'];
    $total_paginas = ceil($total_vinilos / $vinilos_por_pagina);

    // Obtener vinilos
    $sql = "SELECT * FROM vinilos WHERE visible = 1" . $where_busqueda . " ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    if (!empty($busqueda)) {
        $stmt->bind_param('ssii', $busqueda_param, $busqueda_param, $vinilos_por_pagina, $offset);
    } else {
        $stmt->bind_param("ii", $vinilos_por_pagina, $offset);
    }

    $stmt->execute();
    $vinilos = $stmt->get_result();

    // Construir HTML
    $html_vinilos = '';

    if ($vinilos && $vinilos->num_rows > 0) {
        while ($row = $vinilos->fetch_assoc()) {
            $id = (int)$row['id'];
            $nombre = htmlspecialchars($row['nombre'], ENT_QUOTES);
            $precio = number_format($row['precio'], 2, ',', '.');
            $imagen = !empty($row['imagen']) ? htmlspecialchars($row['imagen'], ENT_QUOTES) : '';
            
            $html_vinilos .= '<div class="col-12 col-sm-6 col-md-4 col-lg-3">';
            $html_vinilos .= '<div class="card h-100 shadow-sm" style="background-color: rgba(255,255,255,0.9); border: none; cursor: pointer; transition: transform 0.3s ease;" onclick="window.location.href=\'detalle_vinilo.php?id=' . $id . '\';" onmouseover="this.style.transform=\'translateY(-5px)\'" onmouseout="this.style.transform=\'translateY(0)\'">';
            
            if (!empty($imagen)) {
                $html_vinilos .= '<img src="' . $imagen . '" class="card-img-top" alt="' . $nombre . '" style="object-fit: cover; height: 300px; width: 100%;">';
            } else {
                $html_vinilos .= '<div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 300px;"><i class="bi bi-music-note-beamed" style="font-size: 4rem; color: rgba(255,255,255,0.5);"></i></div>';
            }
            
            $html_vinilos .= '<div class="card-body d-flex flex-column">';
            $html_vinilos .= '<h5 class="card-title" style="font-family: \'Bebas Neue\', sans-serif;">' . $nombre . '</h5>';
            $html_vinilos .= '<p class="card-text mb-3">' . $precio . ' €</p>';
            $html_vinilos .= '<div class="mt-auto d-flex flex-column gap-2">';
            $html_vinilos .= '<div class="d-flex gap-2">';
            $html_vinilos .= '<a href="detalle_vinilo.php?id=' . $id . '" class="btn btn-sm flex-grow-1" style="background: linear-gradient(135deg, #daa520, #b8860b); color: white; font-weight: 600; border: none;" onclick="event.stopPropagation();"><i class="bi bi-eye me-1"></i> Ver detalles</a>';
            $html_vinilos .= '<a href="https://vinyl-labs.vercel.app/formulario.html?vinilo_id=' . $id . '&vinilo_nombre=' . urlencode($row['nombre']) . '" class="btn btn-resena btn-sm" onclick="event.stopPropagation();"><i class="bi bi-star"></i></a>';
            $html_vinilos .= '</div>';
            $html_vinilos .= '<a href="gestionar_carrito.php?accion=agregar&id=' . $id . '" class="btn btn-add-cart btn-sm w-100" onclick="event.stopPropagation();"><i class="bi bi-cart-plus me-1"></i> Añadir al carrito</a>';
            $html_vinilos .= '</div></div></div></div>';
        }
    } else {
        $html_vinilos = '<div class="col-12 text-center"><p class="text-muted">No hay vinilos disponibles' . (!empty($busqueda) ? ' que coincidan con tu búsqueda' : ' en este momento') . '.</p></div>';
    }

    // Construir paginación
    $html_paginacion = '';
    
    if ($total_paginas > 1) {
        $html_paginacion .= '<span class="pagination-info">Página ' . $pagina_actual . ' de ' . $total_paginas . ' (' . $total_vinilos . ' vinilo' . ($total_vinilos != 1 ? 's' : '') . ' en total)</span>';
        $html_paginacion .= '<ul class="pagination">';
        
        // Botón anterior
        $disabled = $pagina_actual <= 1 ? 'disabled' : '';
        $html_paginacion .= '<li class="page-item ' . $disabled . '"><a class="page-link" href="?pagina=' . ($pagina_actual - 1) . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '"><i class="bi bi-chevron-left"></i></a></li>';
        
        $rango = 2;
        $inicio = max(1, $pagina_actual - $rango);
        $fin = min($total_paginas, $pagina_actual + $rango);

        if ($inicio > 1) {
            $html_paginacion .= '<li class="page-item"><a class="page-link" href="?pagina=1' . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">1</a></li>';
            if ($inicio > 2) $html_paginacion .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for ($i = $inicio; $i <= $fin; $i++) {
            $active = $i === $pagina_actual ? 'active' : '';
            $html_paginacion .= '<li class="page-item ' . $active . '"><a class="page-link" href="?pagina=' . $i . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">' . $i . '</a></li>';
        }

        if ($fin < $total_paginas) {
            if ($fin < $total_paginas - 1) $html_paginacion .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            $html_paginacion .= '<li class="page-item"><a class="page-link" href="?pagina=' . $total_paginas . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '">' . $total_paginas . '</a></li>';
        }

        $disabled = $pagina_actual >= $total_paginas ? 'disabled' : '';
        $html_paginacion .= '<li class="page-item ' . $disabled . '"><a class="page-link" href="?pagina=' . ($pagina_actual + 1) . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . '"><i class="bi bi-chevron-right"></i></a></li>';
        $html_paginacion .= '</ul>';
    }

    $stmt->close();
    $stmt_count->close();
    $conn->close();

    // Enviar JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'html' => $html_vinilos,
        'paginacion' => $html_paginacion,
        'total' => $total_vinilos,
        'total_paginas' => $total_paginas,
        'pagina_actual' => $pagina_actual
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'html' => '<div class="col-12 text-center"><p class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</p></div>',
        'paginacion' => '',
        'total' => 0,
        'total_paginas' => 0,
        'pagina_actual' => 1
    ]);
}
exit;
?>