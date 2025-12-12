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
$success = '';

// Get client data
try {
    $sql = "SELECT * FROM clientes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        $error = "Cliente no encontrado";
    }
} catch (PDOException $e) {
    $error = "Error al cargar cliente: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {
    $nombre = trim($_POST["nombre"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $telefono = trim($_POST["telefono"] ?? '');
    $direccion = trim($_POST["direccion"] ?? '');
    
    // Validation
    if (empty($nombre)) {
        $error = "El nombre es requerido";
    } elseif (strlen($nombre) < 3) {
        $error = "El nombre debe tener al menos 3 caracteres";
    } elseif (empty($email)) {
        $error = "El email es requerido";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido";
    } elseif (empty($telefono)) {
        $error = "El teléfono es requerido";
    } elseif (empty($direccion)) {
        $error = "La dirección es requerida";
    }

    if (!$error) {
        // Image upload
        $imagen = $cliente["imagen"];
        
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
                
                // Delete old image if exists and different
                if (!empty($cliente["imagen"]) && file_exists($imagen_dir . "/" . $cliente["imagen"])) {
                    unlink($imagen_dir . "/" . $cliente["imagen"]);
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
                $sql = "UPDATE clientes SET nombre=:nombre, email=:email, telefono=:telefono, direccion=:direccion, imagen=:imagen WHERE id=:id";
                $stmt = $conn->prepare($sql);
                
                $stmt->bindParam(":nombre", $nombre);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":telefono", $telefono);
                $stmt->bindParam(":direccion", $direccion);
                $stmt->bindParam(":imagen", $imagen);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    header("Location: readcliente.php");
                    exit();
                } else {
                    $error = "Error al actualizar cliente";
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
    <title>Editar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
                        <!-- Dashboard Button -->
            <div class="mb-3">
                <a href="/ProyectoWeb/views/dashboard.php" class="btn btn-outline-primary btn-sm">
                    🏠 Ir al Dashboard
                </a>
            </div>            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">✏️ Editar Cliente</h4>
                </div>

                <div class="card-body">

                    <?php if (!$error && $cliente): ?>

                    <form method="POST" enctype="multipart/form-data">

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>" 
                                   required minlength="3">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($cliente['email'], ENT_QUOTES, 'UTF-8') ?>" 
                                   required>
                        </div>

                        <!-- Teléfono -->
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                   value="<?= htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8') ?>" 
                                   required>
                        </div>

                        <!-- Dirección -->
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?= htmlspecialchars($cliente['direccion'], ENT_QUOTES, 'UTF-8') ?>" 
                                   required>
                        </div>

                        <!-- Imagen actual -->
                        <div class="mb-3">
                            <label class="form-label">Imagen Actual</label>
                            <div>
                                <?php if (!empty($cliente['imagen'])): ?>
                                    <img src="<?= htmlspecialchars($url_base . 'modules/clientes/imagen/' . $cliente['imagen'], ENT_QUOTES, 'UTF-8') ?>" 
                                         width="120" class="rounded shadow-sm mb-3" alt="Imagen cliente"
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
                            <a href="readcliente.php" class="btn btn-secondary btn-lg">
                                ← Volver
                            </a>
                        </div>

                    </form>

                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <a href="readcliente.php" class="btn btn-secondary">← Volver</a>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
?>
