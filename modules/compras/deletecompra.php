<?php
session_start();
require_once __DIR__ . "/../../config/config.php";

// Verify authentication and admin role
if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

if ($_SESSION['usuario']['tipo'] !== 'Admin') {
    header("Location: readcompras.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: readcompras.php");
    exit();
}

$id = (int) $_GET['id'];

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Obtener la compra para conocer producto_id y cantidad
    $stmt = $conn->prepare("SELECT producto_id, cantidad FROM compras WHERE id = :id FOR UPDATE");
    $stmt->execute([':id' => $id]);
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        // Si no existe la compra, revertir y redirigir
        $conn->rollBack();
        header("Location: readcompras.php");
        exit();
    }

    $producto_id = (int) $compra['producto_id'];
    $cantidad = (int) $compra['cantidad'];

    // Eliminar la compra
    $del = $conn->prepare("DELETE FROM compras WHERE id = :id");
    $del->execute([':id' => $id]);

    // Restar del stock la cantidad que se había sumado con la compra
    $upd = $conn->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :pid");
    $upd->execute([':cantidad' => $cantidad, ':pid' => $producto_id]);

    // Commit
    $conn->commit();

    header("Location: readcompras.php");
    exit();
} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    // Mostrar error simple y volver al listado (puedes adaptar el manejo)
    echo "Error al eliminar la compra: " . htmlspecialchars($e->getMessage());
    exit();
}
