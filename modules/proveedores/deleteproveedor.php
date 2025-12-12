<?php
session_start();
require_once __DIR__ . "/../../config/config.php";

// Verify authentication and admin role
if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

if ($_SESSION['usuario']['tipo'] !== 'Admin') {
    header("Location: readproveedor.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: readproveedor.php"); exit; }

// obtener imagen actual
$stmt = $conn->prepare("SELECT imagen FROM proveedores WHERE id = :id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// eliminar registro
$del = $conn->prepare("DELETE FROM proveedores WHERE id = :id");
$del->execute([':id' => $id]);

// eliminar archivo de imagen si existe
if ($row && !empty($row['imagen']) && file_exists("imagen/".$row['imagen'])) {
    @unlink("imagen/".$row['imagen']);
}

header("Location: readproveedor.php");
exit();
?>
