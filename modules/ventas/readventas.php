<?php
session_start();
require_once __DIR__ . "/../../config/config.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

// Consulta SQL
$sql = "SELECT * FROM ventas ORDER BY id DESC";

try {
    $result = $conn->query($sql);
} catch (PDOException $e) {
    die("Error en la consulta SQL: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas Registradas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">💰 Ventas</h4>
            <a href="createventas.php" class="btn btn-success btn-sm">
                ➕ Nueva Venta
            </a>
        </div>

        <div class="card-body">
            
            <?php 
            $ventas = $result->fetchAll(PDO::FETCH_ASSOC);
            if (empty($ventas)): 
            ?>
                <div class="alert alert-info">No hay ventas registradas.</div>
            <?php else: ?>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente ID</th>
                        <th>Fecha Venta</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($ventas as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['cliente_id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['fecha_venta'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>$<?= number_format(floatval($row['total']), 2) ?></td>
                        <td><?= htmlspecialchars($row['estado'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td>
                            <div class="btn-group" role="group">
                                <a href="updateventas.php?id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning btn-sm">✏ Editar</a>
                                <?php if ($_SESSION['usuario']['tipo'] === 'Admin'): ?>
                                    <a href="deleteventas.php?id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('¿Seguro de eliminar esta venta?')">
                                       🗑 Eliminar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>

            </table>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
