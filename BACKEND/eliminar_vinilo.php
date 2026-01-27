<?php
session_start();
require_once __DIR__ . '/conexion.php';


if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

$id = $_GET['id'];

// Borrar imagen
$res = $conn->query("SELECT imagen FROM vinilos WHERE id = $id");
$img = $res->fetch_assoc()['imagen'];
if (file_exists($img)) unlink($img);

// Borrar registro
$conn->query("DELETE FROM vinilos WHERE id = $id");

header("Location: gestionar_catalogo.php");
exit();
