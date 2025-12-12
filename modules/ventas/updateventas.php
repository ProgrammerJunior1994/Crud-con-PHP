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

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$error = '';

// Get sale data
try {
    $sql = "SELECT * FROM ventas WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$venta) {
        $error = "Venta no encontrada";
    }
} catch (PDOException $e) {
    $error = "Error al cargar venta: " . $e->getMessage();
}

// Get clients and products
try {
    $clientes = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    $productos = $conn->query("SELECT id, nombre, precio, stock FROM productos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {
    $cliente_id = isset($_POST["cliente_id"]) ? intval($_POST["cliente_id"]) : 0;
    $producto_id = isset($_POST["producto_id"]) ? intval($_POST["producto_id"]) : 0;
    $cantidad = isset($_POST["cantidad"]) ? intval($_POST["cantidad"]) : 0;
    $estado = trim($_POST["estado"] ?? '');
    
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
            // Get product price for total calculation
            $prod_stmt = $conn->prepare("SELECT precio FROM productos WHERE id = :id");
            $prod_stmt->bindParam(":id", $producto_id, PDO::PARAM_INT);
            $prod_stmt->execute();
            $producto_precio = $prod_stmt->fetchColumn();
            $total = $cantidad * $producto_precio;

            // Check stock availability
            $old_cantidad = $venta['cantidad'];
            $old_producto_id = $venta['producto_id'];
            
            // If product changed or quantity increased, verify stock
            if ($producto_id != $old_producto_id || $cantidad > $old_cantidad) {
                $stock_check = $conn->prepare("SELECT stock FROM productos WHERE id = :id");
                $stock_check->bindParam(":id", $producto_id, PDO::PARAM_INT);
                $stock_check->execute();
                $available_stock = $stock_check->fetchColumn();
                
                // Calculate required stock
                $stock_needed = ($producto_id == $old_producto_id) ? $cantidad - $old_cantidad : $cantidad;
                
                if ($available_stock < $stock_needed) {
                    $error = "Stock insuficiente. Stock disponible: " . $available_stock;
                }
            }

            if (!$error) {
                // Begin transaction
                $conn->beginTransaction();

                // If product changed, restore old product stock
                if ($producto_id != $old_producto_id) {
                    $restore = $conn->prepare("UPDATE productos SET stock = stock + :qty WHERE id = :id");
                    $restore->bindParam(":qty", $old_cantidad, PDO::PARAM_INT);
                    $restore->bindParam(":id", $old_producto_id, PDO::PARAM_INT);
                    $restore->execute();
                } else if ($cantidad != $old_cantidad) {
                    // If same product but quantity changed, adjust stock
                    $qty_diff = $old_cantidad - $cantidad;
                    $adjust = $conn->prepare("UPDATE productos SET stock = stock + :qty WHERE id = :id");
                    $adjust->bindParam(":qty", $qty_diff, PDO::PARAM_INT);
                    $adjust->bindParam(":id", $producto_id, PDO::PARAM_INT);
                    $adjust->execute();
                }

                // Update sale
                $sql = "UPDATE ventas SET cliente_id=:cliente_id, producto_id=:producto_id, cantidad=:cantidad, total=:total, estado=:estado WHERE id=:id";
                $stmt = $conn->prepare($sql);
                
                $stmt->bindParam(":cliente_id", $cliente_id, PDO::PARAM_INT);
                $stmt->bindParam(":producto_id", $producto_id, PDO::PARAM_INT);
                $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
                $stmt->bindParam(":total", $total);
                $stmt->bindParam(":estado", $estado);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);

                // Deduct new stock
                $deduct = $conn->prepare("UPDATE productos SET stock = stock - :qty WHERE id = :id");
                $deduct->bindParam(":qty", $cantidad, PDO::PARAM_INT);
                $deduct->bindParam(":id", $producto_id, PDO::PARAM_INT);
                $deduct->execute();

                if ($stmt->execute()) {
                    $conn->commit();
                    header("Location: readventas.php");
                    exit();
                } else {
                    $conn->rollBack();
                    $error = "Error al actualizar venta";
                }
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
    <title>Editar Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            
            <!-- Dashboard Button -->
            <div class="mb-3">
                <a href="/ProyectoWeb/views/dashboard.php" class="btn btn-outline-primary btn-sm">
                    🏠 Ir al Dashboard
                </a>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">✏️ Editar Venta</h4>
                </div>

                <div class="card-body">

                    <?php if (!$error && $venta): ?>

                    <form method="POST">

                        <!-- Cliente -->
                        <div class="mb-3">
                            <label for="cliente_id" class="form-label">Cliente *</label>
                            <select class="form-select" id="cliente_id" name="cliente_id" required>
                                <option value="">-- Seleccione un cliente --</option>
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?= htmlspecialchars($cli['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                            <?= $cli['id'] == $venta['cliente_id'] ? 'selected' : '' ?>>
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
                                            <?= $prod['id'] == $venta['producto_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prod['nombre'], ENT_QUOTES, 'UTF-8') ?> 
                                        (Stock: <?= htmlspecialchars($prod['stock'], ENT_QUOTES, 'UTF-8') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cantidad -->
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                   value="<?= htmlspecialchars($venta['cantidad'], ENT_QUOTES, 'UTF-8') ?>" 
                                   min="1" required>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="Pendiente" <?= $venta['estado'] == 'Pendiente' ? 'selected' : '' ?>>
                                    Pendiente
                                </option>
                                <option value="Completada" <?= $venta['estado'] == 'Completada' ? 'selected' : '' ?>>
                                    Completada
                                </option>
                                <option value="Cancelada" <?= $venta['estado'] == 'Cancelada' ? 'selected' : '' ?>>
                                    Cancelada
                                </option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-between">
                            <button type="submit" class="btn btn-info btn-lg text-white">
                                💾 Actualizar
                            </button>
                            <a href="readventas.php" class="btn btn-secondary btn-lg">
                                ← Volver
                            </a>
                        </div>

                    </form>

                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <a href="readventas.php" class="btn btn-secondary">← Volver</a>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
