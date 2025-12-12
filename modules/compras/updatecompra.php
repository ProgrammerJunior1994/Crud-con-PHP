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

// Get purchase data
try {
    $sql = "SELECT * FROM compras WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$compra) {
        $error = "Compra no encontrada";
    }
} catch (PDOException $e) {
    $error = "Error al cargar compra: " . $e->getMessage();
}

// Get products
try {
    $productos = $conn->query("SELECT id, nombre FROM productos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar productos: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {
    $producto_id = isset($_POST["producto_id"]) ? intval($_POST["producto_id"]) : 0;
    $cantidad = isset($_POST["cantidad"]) ? intval($_POST["cantidad"]) : 0;
    $precio = isset($_POST["precio"]) ? floatval($_POST["precio"]) : 0;
    $fecha_compra = trim($_POST["fecha_compra"] ?? '');
    
    // Validation
    if ($producto_id <= 0) {
        $error = "Debe seleccionar un producto";
    } elseif ($cantidad <= 0) {
        $error = "La cantidad debe ser mayor a 0";
    } elseif ($precio <= 0) {
        $error = "El precio debe ser mayor a 0";
    } elseif (empty($fecha_compra)) {
        $error = "La fecha es requerida";
    }

    if (!$error) {
        try {
            $sql = "UPDATE compras SET producto_id=:producto_id, cantidad=:cantidad, precio=:precio, fecha_compra=:fecha_compra WHERE id=:id";
            $stmt = $conn->prepare($sql);
            
            $stmt->bindParam(":producto_id", $producto_id, PDO::PARAM_INT);
            $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            $stmt->bindParam(":precio", $precio);
            $stmt->bindParam(":fecha_compra", $fecha_compra);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: readcompras.php");
                exit();
            } else {
                $error = "Error al actualizar compra";
            }
        } catch (PDOException $e) {
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
    <title>Editar Compra</title>
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
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">✏️ Editar Compra</h4>
                </div>

                <div class="card-body">

                    <?php if (!$error && $compra): ?>

                    <form method="POST">

                        <!-- Producto -->
                        <div class="mb-3">
                            <label for="producto_id" class="form-label">Producto *</label>
                            <select class="form-select" id="producto_id" name="producto_id" required>
                                <option value="">-- Seleccione un producto --</option>
                                <?php foreach ($productos as $prod): ?>
                                    <option value="<?= htmlspecialchars($prod['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                            <?= $prod['id'] == $compra['producto_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prod['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cantidad -->
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                   value="<?= htmlspecialchars($compra['cantidad'], ENT_QUOTES, 'UTF-8') ?>" 
                                   min="1" required>
                        </div>

                        <!-- Precio Unitario -->
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio *</label>
                            <input type="number" class="form-control" id="precio" name="precio" 
                                   value="<?= isset($compra['precio']) && $compra['precio'] ? floatval($compra['precio']) : '' ?>" 
                                   step="0.01" min="0.01" required>
                        </div>

                        <!-- Fecha -->
                        <div class="mb-3">
                            <label for="fecha_compra" class="form-label">Fecha *</label>
                            <input type="date" class="form-control" id="fecha_compra" name="fecha_compra" 
                                   value="<?= isset($compra['fecha_compra']) && !empty($compra['fecha_compra']) ? htmlspecialchars($compra['fecha_compra'], ENT_QUOTES, 'UTF-8') : '' ?>" 
                                   required>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-between">
                            <button type="submit" class="btn btn-primary btn-lg">
                                💾 Actualizar
                            </button>
                            <a href="readcompras.php" class="btn btn-secondary btn-lg">
                                ← Volver
                            </a>
                        </div>

                    </form>

                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <a href="readcompras.php" class="btn btn-secondary">← Volver</a>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
