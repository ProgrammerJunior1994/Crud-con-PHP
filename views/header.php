<?php 
$url_base = "http://localhost/proyectoweb/";
require_once __DIR__ . "/../config/config.php";

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}
$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Sistema ERP - Dashboard</title>
  <link rel="icon" href="imagen/favicon.ico">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #2c3e50;
      --secondary: #3498db;
      --success: #27ae60;
      --warning: #f39c12;
      --danger: #e74c3c;
      --light: #ecf0f1;
      --dark: #2c3e50;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8f9fa;
      overflow-x: hidden;
    }
    
    /* Sidebar Styles */
    .sidebar {
      background: linear-gradient(135deg, var(--primary) 0%, #34495e 100%);
      color: white;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      width: 280px;
      transition: all 0.3s;
      z-index: 1000;
      box-shadow: 3px 0 10px rgba(0,0,0,0.1);
    }
    
    .sidebar.collapsed {
      width: 80px;
    }
    
    .sidebar.collapsed .sidebar-text {
      display: none;
    }
    
    .sidebar-header {
      padding: 20px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .user-info {
      text-align: center;
      padding: 20px 0;
    }
    
    .user-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 3px solid rgba(255,255,255,0.2);
      object-fit: cover;
      margin-bottom: 15px;
    }
    
    .sidebar-nav {
      padding: 20px 0;
    }
    
    .nav-link {
      color: rgba(255,255,255,0.8);
      padding: 12px 25px;
      margin: 5px 0;
      border-radius: 8px;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      text-decoration: none;
    }
    
    .nav-link:hover, .nav-link.active {
      background: rgba(255,255,255,0.1);
      color: white;
      transform: translateX(5px);
    }
    
    .nav-link i {
      width: 20px;
      margin-right: 15px;
      font-size: 1.1em;
    }
    
    /* Main Content */
    .main-content {
      margin-left: 280px;
      transition: all 0.3s;
      min-height: 100vh;
    }
    
    .main-content.expanded {
      margin-left: 80px;
    }
    
    /* Top Navbar */
    .top-navbar {
      background: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 15px 30px;
      position: sticky;
      top: 0;
      z-index: 999;
    }
    
    /* Cards */
    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      transition: all 0.3s;
      border: none;
      height: 100%;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 15px;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    
    .stat-title {
      color: #6c757d;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    /* Charts and Activity */
    .chart-container, .activity-container {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      height: 100%;
    }
    
    .activity-item {
      padding: 15px 0;
      border-bottom: 1px solid #eee;
      display: flex;
      align-items: center;
    }
    
    .activity-item:last-child {
      border-bottom: none;
    }
    
    .activity-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 1rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        width: 80px;
      }
      .sidebar .sidebar-text {
        display: none;
      }
      .main-content {
        margin-left: 80px;
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header text-center">
      <h4 class="mb-0">
        <i class="bi bi-building"></i>
        <span class="sidebar-text ms-2">ERP System</span>
      </h4>
    </div>
    
    <div class="user-info">
      <img src="imagen/<?php echo $usuario['imagen']; ?>" class="user-avatar" alt="Avatar">
      <h6 class="mb-1"><?php echo $usuario['nombre']; ?></h6>
      <small class="text-muted">Administrador</small>
    </div>
    
    <div class="sidebar-nav">
      <a href="readcliente.php" class="nav-link active">
        <i class="bi bi-people-fill"></i>
        <span class="sidebar-text">Clientes</span>
      </a>
      <a href="readproveedores.php" class="nav-link">
        <i class="bi bi-truck"></i>
        <span class="sidebar-text">Proveedores</span>
      </a>
      <a href="readproducto.php" class="nav-link">
        <i class="bi bi-box-seam"></i>
        <span class="sidebar-text">Productos</span>
      </a>
      <a href="readventas.php" class="nav-link">
        <i class="bi bi-cart-check"></i>
        <span class="sidebar-text">Ventas</span>
      </a>
      <a href="readcompras.php" class="nav-link">
        <i class="bi bi-currency-dollar"></i>
        <span class="sidebar-text">Compras</span>
      </a>
      <a href="#" class="nav-link">
        <i class="bi bi-graph-up"></i>
        <span class="sidebar-text">Reportes</span>
      </a>
      <a href="#" class="nav-link">
        <i class="bi bi-gear"></i>
        <span class="sidebar-text">Configuración</span>
      </a>
    </div>
    
    <div class="mt-auto p-3">
      <a href="logout.php" class="nav-link text-danger">
        <i class="bi bi-box-arrow-right"></i>
        <span class="sidebar-text">Cerrar Sesión</span>
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">
    <!-- Top Navbar -->
    <nav class="top-navbar">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <button class="btn btn-outline-primary" id="sidebarToggle">
            <i class="bi bi-list"></i>
          </button>
          <span class="ms-3 fw-bold">Dashboard Principal</span>
        </div>
        
        <div class="d-flex align-items-center">
          <div class="dropdown me-3">
            <button class="btn btn-light position-relative" data-bs-toggle="dropdown">
              <i class="bi bi-bell"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                3
              </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <h6 class="dropdown-header">Notificaciones</h6>
              <a class="dropdown-item" href="#">Nuevo pedido recibido</a>
              <a class="dropdown-item" href="#">Stock bajo en productos</a>
              <a class="dropdown-item" href="#">Actualización del sistema</a>
            </div>
          </div>
          
          <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-2"></i>
              <?php echo explode(' ', $usuario['nombre'])[0]; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">
                <i class="bi bi-person me-2"></i>Mi Perfil
              </a>
              <a class="dropdown-item" href="#">
                <i class="bi bi-gear me-2"></i>Configuración
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
              </a>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <div class="container-fluid p-4">
      <!-- Welcome Banner -->
      <div class="row mb-4">