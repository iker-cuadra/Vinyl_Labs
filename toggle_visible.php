<?php
require 'admin_guard.php';
require 'conexion.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: admin.php");
    exit;
}

$stmt = $conn->prepare("UPDATE vinilos SET visible = IF(visible=1,0,1) WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: admin.php");
exit;
