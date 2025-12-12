<?php
session_start();
require_once __DIR__ . "/../../config/config.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

// Calculate base URL dynamically
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$url_base = $scheme . '://' . $host . '/ProyectoWeb/';

try {
    $sql = "SELECT p.*, pr.nombre AS proveedor 
            FROM productos p
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            ORDER BY p.id DESC";
    $productos = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar productos: " . $e->getMessage();
    $productos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php if (isset($error)): ?>
    <div class="container mt-3">
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
<?php endif; ?>

<div class="container mt-5 mb-5">

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📦 Productos</h4>
            <a href="createproducto.php" class="btn btn-success btn-sm">
                ➕ Nuevo Producto
            </a>
        </div>

        <div class="card-body">

            <?php if (empty($productos)): ?>
                <div class="alert alert-info" role="alert">
                    No hay productos registrados. <a href="createproducto.php">Agregar producto</a>
                </div>
            <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover table-striped text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Proveedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (!empty($p['imagen'])): ?>
                                    <img src="<?= htmlspecialchars($url_base . 'modules/productos/imagen/' . $p['imagen'], ENT_QUOTES, 'UTF-8') ?>" 
                                         width="60" class="rounded shadow-sm" alt="Producto"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22%3E%3Crect fill=%22%23ddd%22 width=%2260%22 height=%2260%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2212%22 fill=%22%23999%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo imagen%3C/text%3E%3C/svg%3E'">
                                <?php else: ?>
                                    <img src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22%3E%3Crect fill=%22%23ddd%22 width=%2260%22 height=%2260%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2212%22 fill=%22%23999%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo imagen%3C/text%3E%3C/svg%3E" width="60" class="rounded shadow-sm">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>$<?= number_format(floatval($p['precio']), 2) ?></td>
                            <td><?= htmlspecialchars($p['stock'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($p['proveedor'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="updateproducto.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning btn-sm">✏ Editar</a>
                                    <?php if ($_SESSION['usuario']['tipo'] === 'Admin'): ?>
                                        <a href="deleteproducto.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro?')">🗑 Eliminar</a>
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
