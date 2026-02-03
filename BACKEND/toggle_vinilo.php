<?php
session_start();
require_once __DIR__ . '/conexion.php';


$id = $_GET['id'];

$conn->query("UPDATE vinilos SET visible = NOT visible WHERE id = $id");

header("Location: https://vinyllabs-production.up.railway.app/gestionar_catalogo.php");
exit();
