<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

$id = $_GET['id'];

$conn->query("UPDATE vinilos SET visible = NOT visible WHERE id = $id");

header("Location: gestionar_catalogo.php");
exit();
