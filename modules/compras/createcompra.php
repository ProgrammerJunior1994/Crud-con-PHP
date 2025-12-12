<?php
require_once __DIR__ . "/../../config/config.php";
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Obtener lista de productos
$productos = [];
$error_message = '';
$success_message = '';

try {
    $result = $conn->query("SELECT id, nombre, precio FROM productos WHERE stock > 0 ORDER BY nombre");
    $productos = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error al obtener productos: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)) {
    $producto_id = intval($_POST['producto_id'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 0);
    $fecha_compra = trim($_POST['fecha_compra'] ?? date('Y-m-d'));

    // Validaciones
    if ($producto_id <= 0) {
        $error_message = "Debes seleccionar un producto válido.";
    } elseif ($cantidad <= 0) {
        $error_message = "La cantidad debe ser mayor a 0.";
    } elseif (empty($fecha_compra)) {
        $error_message = "La fecha de compra es requerida.";
    } else {
        // Validar que la fecha sea válida
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_compra);
        if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha_compra) {
            $error_message = "Formato de fecha inválido (usa YYYY-MM-DD).";
        }
    }

    if (empty($error_message)) {
        try {
            // Obtener precio real del producto
            $stmt = $conn->prepare("SELECT precio, stock FROM productos WHERE id = :id");
            $stmt->execute([':id' => $producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                $error_message = "El producto seleccionado no existe.";
            } elseif ($producto['stock'] < $cantidad) {
                $error_message = "Stock insuficiente. Disponible: " . intval($producto['stock']);
            } else {
                $precio = floatval($producto['precio']);

                // Insertar compra SIN columna 'total' (la tabla no tiene esa columna)
                $sql = "INSERT INTO compras (fecha_compra, producto_id, cantidad, precio) 
                        VALUES (:fecha_compra, :producto_id, :cantidad, :precio)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':fecha_compra' => $fecha_compra,
                    ':producto_id' => $producto_id,
                    ':cantidad' => $cantidad,
                    ':precio' => $precio
                ]);

                $success_message = "Compra registrada exitosamente.";
                // Redirigir después de 2 segundos
                header("Refresh: 2; url=readcompras.php");
            }
        } catch (PDOException $e) {
            $error_message = "Error al guardar la compra: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Compra</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-4">
    <div class="mb-3">
        <a href="/ProyectoWeb/views/dashboard.php" class="btn btn-outline-primary btn-sm">
            🏠 Ir al Dashboard
        </a>
    </div>
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Registrar Nueva Compra</h2>
        </div>
        <div class="card-body">

            <!-- Mensajes de error/éxito -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?= $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Éxito:</strong> <?= $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Producto <span class="text-danger">*</span></label>
                    <select name="producto_id" class="form-select" required>
                        <option value="">Seleccione un producto</option>
                        <?php foreach ($productos as $prod): ?>
                            <option value="<?= htmlspecialchars($prod['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                <?= (isset($_POST['producto_id']) && $_POST['producto_id'] == $prod['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($prod['nombre'], ENT_QUOTES, 'UTF-8'); ?> — 
                                $<?= number_format(floatval($prod['precio']), 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($productos)): ?>
                        <small class="text-warning">No hay productos disponibles.</small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" name="cantidad" class="form-control" min="1" 
                        value="<?= htmlspecialchars($_POST['cantidad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_compra" class="form-control" 
                        value="<?= htmlspecialchars($_POST['fecha_compra'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Compra</button>
                    <a href="readcompras.php" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
