<?php

require_once __DIR__ . '/conexion.php';



$buscar = $_GET['buscar'] ?? '';
$like = "%$buscar%";

$sql = "SELECT * FROM vinilos WHERE nombre LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();

while ($v = $result->fetch_assoc()):
  ?>
  <tr>
    <td>
      <img src="<?= htmlspecialchars($v['imagen']) ?>"
        style="width:70px;border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,.2);">
    </td>

    <td style="font-weight:600;">
      <?= htmlspecialchars($v['nombre']) ?>
    </td>

    <td>
      <?= number_format($v['precio'], 2, ',', '.') ?> €
    </td>

    <td>
      <span class="badge <?= $v['visible'] ? 'bg-success' : 'bg-secondary' ?>">
        <?= $v['visible'] ? 'Visible' : 'Oculto' ?>
      </span>
    </td>

    <td class="d-flex gap-2 justify-content-center">
      <a href="toggle_vinilo.php?id=<?= $v['id'] ?>" class="btn btn-sm" style="background-color:#c48a3a;color:white;">
        <?= $v['visible'] ? 'Ocultar' : 'Mostrar' ?>
      </a>

      <a href="eliminar_vinilo.php?id=<?= $v['id'] ?>" class="btn btn-danger btn-sm"
        onclick="return confirm('¿Eliminar este vinilo?')">
        Eliminar
      </a>
    </td>
  </tr>
<?php endwhile; ?>