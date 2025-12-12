<?php
session_start();
require_once __DIR__ . "/../../config/config.php";

// Verify authentication
if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

// Calculate base URL dynamically
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$url_base = $scheme . '://' . $host . '/ProyectoWeb/';

$error = '';
$form_data = [];

// Obtain products and clients
$productos = $conn->query("SELECT id, nombre, precio, stock FROM productos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$clientes = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
    $producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
    $estado = trim($_POST['estado'] ?? '');

    // Store form data for repopulation
    $form_data = compact('cliente_id', 'producto_id', 'cantidad', 'estado');

    // Validation
    if ($cliente_id <= 0) {
        $error = "Debe seleccionar un cliente";
    } elseif ($producto_id <= 0) {
        $error = "Debe seleccionar un producto";
    } elseif ($cantidad <= 0) {
        $error = "La cantidad debe ser mayor a 0";
    } elseif (empty($estado)) {
        $error = "Debe seleccionar un estado";
    }

    if (!$error) {
        try {
            // Get product price and stock
            $stmt = $conn->prepare("SELECT precio, stock FROM productos WHERE id = :id");
            $stmt->bindParam(':id', $producto_id, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $error = "Producto no encontrado";
            } elseif ($product['stock'] < $cantidad) {
                $error = "Stock insuficiente. Stock disponible: " . $product['stock'];
            }

            if (!$error) {
                $total = floatval($product['precio']) * $cantidad;

                // Begin transaction
                $conn->beginTransaction();

                // Insert venta
                $sql = "INSERT INTO ventas (cliente_id, producto_id, cantidad, total, estado, fecha_venta)
                        VALUES (:cliente_id, :producto_id, :cantidad, :total, :estado, NOW())";
                $stmt = $conn->prepare($sql);
                
                $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
                $stmt->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
                $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                $stmt->bindParam(':total', $total);
                $stmt->bindParam(':estado', $estado);
                
                $stmt->execute();

                // Update stock
                $updateStmt = $conn->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :id");
                $updateStmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                $updateStmt->bindParam(':id', $producto_id, PDO::PARAM_INT);
                $updateStmt->execute();

                // Commit transaction
                $conn->commit();

                header("Location: readventas.php");
                exit();
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7">

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">💰 Nueva Venta</h4>
                </div>

                <div class="card-body">

                    <form method="POST">

                        <!-- Cliente -->
                        <div class="mb-3">
                            <label for="cliente_id" class="form-label">Cliente *</label>
                            <select class="form-select" id="cliente_id" name="cliente_id" required>
                                <option value="">-- Seleccione un cliente --</option>
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?= htmlspecialchars($cli['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                            <?= ($form_data['cliente_id'] ?? 0) == $cli['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cli['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Producto -->
                        <div class="mb-3">
                            <label for="producto_id" class="form-label">Producto *</label>
                            <select class="form-select" id="producto_id" name="producto_id" required>
                                <option value="">-- Seleccione un producto --</option>
                                <?php foreach ($productos as $prod): ?>
                                    <option value="<?= htmlspecialchars($prod['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                            <?= ($form_data['producto_id'] ?? 0) == $prod['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prod['nombre'], ENT_QUOTES, 'UTF-8') ?> 
                                        (Precio: $<?= number_format(floatval($prod['precio']), 2) ?>, Stock: <?= $prod['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cantidad -->
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                   value="<?= htmlspecialchars($form_data['cantidad'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                   min="1" required>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="">-- Seleccione un estado --</option>
                                <option value="Pendiente" <?= ($form_data['estado'] ?? '') == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="Completada" <?= ($form_data['estado'] ?? '') == 'Completada' ? 'selected' : '' ?>>Completada</option>
                                <option value="Cancelada" <?= ($form_data['estado'] ?? '') == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-between">
                            <button type="submit" class="btn btn-info btn-lg text-white">
                                ✅ Guardar
                            </button>
                            <a href="readventas.php" class="btn btn-secondary btn-lg">
                                ← Volver
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
