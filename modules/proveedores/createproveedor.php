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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');

    // Store form data for repopulation
    $form_data = compact('nombre', 'email', 'telefono', 'direccion', 'empresa');

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
    } elseif (empty($empresa)) {
        $error = "El nombre de empresa es requerido";
    } elseif (empty($direccion)) {
        $error = "La dirección es requerida";
    }

    if (!$error) {
        // Image upload
        $imagen = '';
        
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
                $sql = "INSERT INTO proveedores (nombre, email, telefono, direccion, empresa, imagen)
                        VALUES (:nombre, :email, :telefono, :direccion, :empresa, :imagen)";
                $stmt = $conn->prepare($sql);
                
                $stmt->bindParam(":nombre", $nombre);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":telefono", $telefono);
                $stmt->bindParam(":direccion", $direccion);
                $stmt->bindParam(":empresa", $empresa);
                $stmt->bindParam(":imagen", $imagen);
                
                if ($stmt->execute()) {
                    header("Location: readproveedor.php");
                    exit();
                } else {
                    $error = "Error al crear proveedor";
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Proveedor</title>
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
            <?php?>

            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">🏢 Nuevo Proveedor</h4>
                </div>

                <div class="card-body">

                    <form method="POST" enctype="multipart/form-data">

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($form_data['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                   required minlength="3">
                        </div>

                        <!-- Empresa -->
                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa *</label>
                            <input type="text" class="form-control" id="empresa" name="empresa" 
                                   value="<?= htmlspecialchars($form_data['empresa'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($form_data['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                   required>
                        </div>

                        <!-- Teléfono -->
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                   value="<?= htmlspecialchars($form_data['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                   required>
                        </div>

                        <!-- Dirección -->
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección *</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?= htmlspecialchars($form_data['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                   required>
                        </div>

                        <!-- Imagen -->
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen (Opcional)</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" 
                                   accept=".jpg,.jpeg,.png,.gif,.webp">
                            <small class="text-muted">Formatos: JPEG, PNG, GIF, WebP. Máximo 5 MB</small>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-between">
                            <button type="submit" class="btn btn-success btn-lg">
                                ✅ Guardar
                            </button>
                            <a href="readproveedor.php" class="btn btn-secondary btn-lg">
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
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
