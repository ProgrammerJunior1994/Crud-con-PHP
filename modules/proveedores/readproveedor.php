<?php
session_start();
require_once __DIR__ . "/../../config/config.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: /ProyectoWeb/auth/login.php");
    exit();
}

$stmt = $conn->query("SELECT * FROM proveedores ORDER BY id DESC");
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proveedores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">🏢 Proveedores</h4>
      <a href="createproveedor.php" class="btn btn-success btn-sm">
        ➕ Nuevo Proveedor
      </a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr class="text-center">
              <th>ID</th>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Empresa</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($proveedores) === 0): ?>
              <tr><td colspan="8" class="text-center p-4">No hay proveedores registrados.</td></tr>
            <?php else: ?>
              <?php foreach ($proveedores as $p): ?>
                <tr>
                  <td class="text-center"><?= htmlspecialchars($p['id']) ?></td>
                  <td class="text-center">
                    <?php if (!empty($p['imagen']) && file_exists("imagen/".$p['imagen'])): ?>
                      <img src="imagen/<?= htmlspecialchars($p['imagen']) ?>" alt="" width="64" class="rounded">
                    <?php else: ?>
                      <span class="text-muted small">Sin imagen</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($p['nombre']) ?></td>
                  <td><?= htmlspecialchars($p['empresa']) ?></td>
                  <td><?= htmlspecialchars($p['email']) ?></td>
                  <td><?= htmlspecialchars($p['telefono']) ?></td>
                  <td><?= htmlspecialchars($p['direccion']) ?></td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <a href="updateproveedor.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-warning">✏ Editar</a>
                      <?php if ($_SESSION['usuario']['tipo'] === 'Admin'): ?>
                          <a href="deleteproveedor.php?id=<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar proveedor?')">🗑 Eliminar</a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
