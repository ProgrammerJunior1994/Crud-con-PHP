<?php
require_once __DIR__ . "/../../config/config.php";
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Consulta con JOIN para mostrar nombre del producto
$sql = "SELECT compras.id, compras.fecha_compra, compras.producto_id, 
               compras.cantidad, compras.precio, productos.nombre AS producto
        FROM compras
        INNER JOIN productos ON compras.producto_id = productos.id
        ORDER BY compras.fecha_compra DESC";

$error_message = '';
$result = [];

try {
    $result = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error en la consulta SQL: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Compras</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📋 Compras</h4>
            <a href="createcompra.php" class="btn btn-success btn-sm">
                ➕ Nueva Compra
            </a>
        </div>
        <div class="card-body">
            
            <!-- Mensajes de error -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?= $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($result)): ?>
                <div class="alert alert-info">No hay compras registradas.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Fecha de Compra</th>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $gran_total = 0;
                            foreach ($result as $row): 
                                $total = floatval($row['cantidad']) * floatval($row['precio']);
                                $gran_total += $total;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['fecha_compra'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['producto'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-end"><?= intval($row['cantidad']); ?></td>
                                    <td class="text-end">$<?= number_format(floatval($row['precio']), 2); ?></td>
                                    <td class="text-end fw-bold">$<?= number_format($total, 2); ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="updatecompra.php?id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>" 
                                               class="btn btn-warning btn-sm">✏ Editar</a>
                                            <?php if ($_SESSION['usuario']['tipo'] === 'Admin'): ?>
                                                <a href="deletecompra.php?id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('¿Seguro de eliminar esta compra?')">
                                                   🗑 Eliminar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="5" class="text-end">TOTAL COMPRAS:</th>
                                <th class="text-end">$<?= number_format($gran_total, 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
