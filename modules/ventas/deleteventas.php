<?php
session_start();
require_once __DIR__ . "/../../config/config.php";

// Verify authentication and admin role
if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

if ($_SESSION['usuario']['tipo'] !== 'Admin') {
    header("Location: readventas.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: readventas.php");
    exit();
}

$id = (int) $_GET['id'];

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Obtener venta (producto y cantidad)
    $stmt = $conn->prepare("SELECT producto_id, cantidad FROM ventas WHERE id = :id FOR UPDATE");
    $stmt->execute([':id' => $id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        $conn->rollBack();
        header("Location: readventas.php");
        exit();
    }

    $producto_id = (int) $venta['producto_id'];
    $cantidad = (int) $venta['cantidad'];

    // Eliminar venta
    $del = $conn->prepare("DELETE FROM ventas WHERE id = :id");
    $del->execute([':id' => $id]);

    // Devolver stock (sumar la cantidad vendida)
    $upd = $conn->prepare("UPDATE productos SET stock = stock + :cantidad WHERE id = :pid");
    $upd->execute([
        ':cantidad' => $cantidad,
        ':pid' => $producto_id
    ]);

    // Confirmar transacción
    $conn->commit();

    header("Location: readventas.php");
    exit();
} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo "Error al eliminar la venta: " . htmlspecialchars($e->getMessage());
    exit();
}
