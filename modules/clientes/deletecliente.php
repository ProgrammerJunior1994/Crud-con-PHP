<?php
// deletecliente.php
session_start();
require_once __DIR__ . "/../../config/config.php";

// Verify authentication and admin role
if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

if ($_SESSION['usuario']['tipo'] !== 'Admin') {
    header("Location: readcliente.php");
    exit();
}

// Obtener id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: readcliente.php");
    exit();
}

// Obtener el nombre del archivo de imagen (si existe)
$stmt = $conn->prepare("SELECT imagen FROM clientes WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $imagen = $row['imagen'];

    // Borrar la fila de la base de datos
    $del = $conn->prepare("DELETE FROM clientes WHERE id = :id");
    $del->bindParam(':id', $id, PDO::PARAM_INT);

    if ($del->execute()) {
        // Si la imagen existe y no está vacía, eliminar el archivo físico
        if (!empty($imagen) && file_exists("imagen/" . $imagen)) {
            @unlink("imagen/" . $imagen);
        }
    }
}

// Volver al listado
header("Location: readcliente.php");
exit();
?>
