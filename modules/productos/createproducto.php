<?php
require_once __DIR__ . "/../../config/config.php";
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Obtener lista de proveedores
$proveedores = $conn->query("SELECT * FROM proveedores")->fetchAll(PDO::FETCH_ASSOC);
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');
    $precio = $_POST["precio"] ?? 0;
    $stock = $_POST["stock"] ?? 0;
    $proveedor_id = $_POST["proveedor_id"] ?? null;

    // Validaciones
    if (empty($nombre)) {
        $error_message = "El nombre del producto es requerido.";
    } elseif (empty($descripcion)) {
        $error_message = "La descripción es requerida.";
    } elseif ($precio <= 0) {
        $error_message = "El precio debe ser mayor a 0.";
    } elseif ($stock < 0) {
        $error_message = "El stock no puede ser negativo.";
    } elseif (empty($proveedor_id)) {
        $error_message = "Debes seleccionar un proveedor.";
    }

    if (empty($error_message)) {
        // Procesar imagen
        $imagen = "default.jpg";
        if (!empty($_FILES["imagen"]["name"])) {
            // Validar tipo de archivo
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES["imagen"]["type"], $allowed_types)) {
                $error_message = "Solo se permiten imágenes (JPEG, PNG, GIF, WEBP).";
            } elseif ($_FILES["imagen"]["size"] > $max_size) {
                $error_message = "La imagen no debe pesar más de 5MB.";
            } else {
                // Crear carpeta si no existe
                $imagen_dir = __DIR__ . "/imagen";
                if (!is_dir($imagen_dir)) {
                    mkdir($imagen_dir, 0755, true);
                }

                // Generar nombre único
                $ext = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
                $nombreImagen = time() . "_" . bin2hex(random_bytes(5)) . "." . $ext;
                $ruta_absoluta = $imagen_dir . DIRECTORY_SEPARATOR . $nombreImagen;

                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta_absoluta)) {
                    $imagen = $nombreImagen;
                } else {
                    $error_message = "Error al guardar la imagen. Verifica permisos de carpeta.";
                }
            }
        }

        // Si no hay error, guardar en BD
        if (empty($error_message)) {
            try {
                $sql = "INSERT INTO productos(nombre, descripcion, precio, stock, imagen, proveedor_id)
                        VALUES(:nombre, :descripcion, :precio, :stock, :imagen, :proveedor_id)";
                $stmt = $conn->prepare($sql);

                $stmt->execute([
                    ":nombre" => $nombre,
                    ":descripcion" => $descripcion,
                    ":precio" => floatval($precio),
                    ":stock" => intval($stock),
                    ":imagen" => $imagen,
                    ":proveedor_id" => intval($proveedor_id)
                ]);

                $success_message = "Producto creado exitosamente.";
                // Redirigir después de 2 segundos
                header("Refresh: 2; url=readproducto.php");
            } catch (PDOException $e) {
                $error_message = "Error al guardar el producto: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h4>Agregar Producto</h4>
        </div>
        <div class="card-body">

            <!-- Mensajes de error/éxito -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Éxito:</strong> <?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <label class="form-label">Nombre:</label>
                <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

                <label class="form-label mt-3">Descripción:</label>
                <textarea class="form-control" name="descripcion" required><?= htmlspecialchars($_POST['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                <label class="form-label mt-3">Precio:</label>
                <input type="number" step="0.01" min="0.01" class="form-control" name="precio" value="<?= htmlspecialchars($_POST['precio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

                <label class="form-label mt-3">Stock:</label>
                <input type="number" min="0" class="form-control" name="stock" value="<?= htmlspecialchars($_POST['stock'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

                <label class="form-label mt-3">Proveedor:</label>
                <select class="form-select" name="proveedor_id" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($proveedores as $prov): ?>
                        <option value="<?= htmlspecialchars($prov['id'], ENT_QUOTES, 'UTF-8'); ?>" 
                            <?= (isset($_POST['proveedor_id']) && $_POST['proveedor_id'] == $prov['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($prov['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label mt-3">Imagen (Opcional - Max 5MB):</label>
                <input type="file" class="form-control" name="imagen" accept="image/jpeg,image/png,image/gif,image/webp">
                <small class="text-muted">Formatos permitidos: JPEG, PNG, GIF, WebP</small>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Guardar Producto</button>
                    <a href="readproducto.php" class="btn btn-secondary">Volver</a>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
