<?php
session_start();
require_once __DIR__ . "/../../config/config.php";

// Verify authentication and admin role
if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

if ($_SESSION['usuario']['tipo'] !== 'Admin') {
    header("Location: readproducto.php");
    exit();
}

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM productos WHERE id = :id");
    $stmt->execute([":id" => $id]);
}

header("Location: readproducto.php");
exit();
?>
