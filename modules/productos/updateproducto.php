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

// Get product data
try {
    $sql = "SELECT p.*, pr.nombre AS proveedor_nombre FROM productos p
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            WHERE p.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        $error = "Producto no encontrado";
    }
} catch (PDOException $e) {
    $error = "Error al cargar producto: " . $e->getMessage();
}

// Get providers
try {
    $proveedores = $conn->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar proveedores: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {
    $nombre = trim($_POST["nombre"] ?? '');
    $descripcion = trim($_POST["descripcion"] ?? '');
    $precio = isset($_POST["precio"]) ? floatval($_POST["precio"]) : 0;
    $stock = isset($_POST["stock"]) ? intval($_POST["stock"]) : 0;
    $proveedor_id = isset($_POST["proveedor_id"]) ? intval($_POST["proveedor_id"]) : 0;
    
    // Validation
    if (empty($nombre)) {
        $error = "El nombre es requerido";
    } elseif (strlen($nombre) < 3) {
        $error = "El nombre debe tener al menos 3 caracteres";
    } elseif (empty($descripcion)) {
        $error = "La descripción es requerida";
    } elseif ($precio <= 0) {
        $error = "El precio debe ser mayor a 0";
    } elseif ($stock < 0) {
        $error = "El stock no puede ser negativo";
    } elseif ($proveedor_id <= 0) {
        $error = "Debe seleccionar un proveedor";
    }

    if (!$error) {
        // Image upload
        $imagen = $producto["imagen"];
        
        if (!empty($_FILES["imagen"]["name"])) {
            $file_name = $_FILES["imagen"]["name"];
            $file_tmp = $_FILES["imagen"]["tmp_name"];
            $file_size = $_FILES["imagen"]["size"];
            $file_type = mime_content_type($file_tmp);
            
            // Validations
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file_type, $allowed_types)) {
                $error = "Tipo de archivo no permitido. Solo JPEG, PNG, GIF, WebP.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error = "El archivo no debe exceder 5 MB";
            } else {
                $imagen_dir = __DIR__ . "/imagen";
                if (!is_dir($imagen_dir)) {
                    mkdir($imagen_dir, 0755, true);
                }
                
                // Delete old image if exists
                if (!empty($producto["imagen"]) && file_exists($imagen_dir . "/" . $producto["imagen"])) {
                    unlink($imagen_dir . "/" . $producto["imagen"]);
                }
                
                $new_name = time() . "_" . basename($file_name);
                if (move_uploaded_file($file_tmp, $imagen_dir . "/" . $new_name)) {
                    $imagen = $new_name;
                } else {
                    $error = "Error al subir la imagen";
                }
            }
        }
        
        if (!$error) {
            try {
                $sql = "UPDATE productos SET nombre=:nombre, descripcion=:descripcion, precio=:precio, stock=:stock, imagen=:imagen, proveedor_id=:proveedor_id WHERE id=:id";
                $stmt = $conn->prepare($sql);
                
                $stmt->bindParam(":nombre", $nombre);
                $stmt->bindParam(":descripcion", $descripcion);
                $stmt->bindParam(":precio", $precio);
                $stmt->bindParam(":stock", $stock);
                $stmt->bindParam(":imagen", $imagen);
                $stmt->bindParam(":proveedor_id", $proveedor_id, PDO::PARAM_INT);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    header("Location: readproducto.php");
                    exit();
                } else {
                    $error = "Error al actualizar producto";
                }
            } catch (PDOException $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
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
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">✏️ Editar Producto</h4>
                </div>

                <div class="card-body">

                    <?php if (!$error && $producto): ?>

                    <form method="POST" enctype="multipart/form-data">

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>" 
                                   required minlength="3">
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3" required><?= htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <!-- Precio -->
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio *</label>
                            <input type="number" class="form-control" id="precio" name="precio" 
                                   value="<?= floatval($producto['precio']) ?>" 
                                   step="0.01" min="0.01" required>
                        </div>

                        <!-- Stock -->
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock *</label>
                            <input type="number" class="form-control" id="stock" name="stock" 
                                   value="<?= htmlspecialchars($producto['stock'], ENT_QUOTES, 'UTF-8') ?>" 
                                   min="0" required>
                        </div>

                        <!-- Proveedor -->
                        <div class="mb-3">
                            <label for="proveedor_id" class="form-label">Proveedor *</label>
                            <select class="form-select" id="proveedor_id" name="proveedor_id" required>
                                <option value="">-- Seleccione un proveedor --</option>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= htmlspecialchars($prov['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                            <?= $prov['id'] == $producto['proveedor_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prov['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Imagen actual -->
                        <div class="mb-3">
                            <label class="form-label">Imagen Actual</label>
                            <div>
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?= htmlspecialchars($url_base . 'modules/productos/imagen/' . $producto['imagen'], ENT_QUOTES, 'UTF-8') ?>" 
                                         width="120" class="rounded shadow-sm mb-3" alt="Imagen producto"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect fill=%22%23ddd%22 width=%22120%22 height=%22120%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2214%22 fill=%22%23999%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo imagen%3C/text%3E%3C/svg%3E'">
                                <?php else: ?>
                                    <p class="text-muted">Sin imagen</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Cambiar imagen -->
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Cambiar Imagen (Opcional)</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" 
                                   accept=".jpg,.jpeg,.png,.gif,.webp">
                            <small class="text-muted">Formatos: JPEG, PNG, GIF, WebP. Máximo 5 MB</small>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-between">
                            <button type="submit" class="btn btn-warning btn-lg">
                                💾 Actualizar
                            </button>
                            <a href="readproducto.php" class="btn btn-secondary btn-lg">
                                ← Volver
                            </a>
                        </div>

                    </form>

                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <a href="readproducto.php" class="btn btn-secondary">← Volver</a>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
