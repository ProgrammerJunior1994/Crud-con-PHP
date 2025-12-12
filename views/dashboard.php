<?php 
// Calcular base URL dinámicamente (más robusto en distintos entornos)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$url_base = $scheme . '://' . $host . '/ProyectoWeb/';
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
      <img src="../imagen/<?php echo htmlspecialchars($usuario['imagen'], ENT_QUOTES, 'UTF-8'); ?>" class="user-avatar" alt="Avatar">
      <h6 class="mb-1"><?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?></h6>
      <small class="text-muted">Administrador</small>
    </div>
    
    <div class="sidebar-nav">
      <a href="../modules/clientes/readcliente.php" class="nav-link">
        <i class="bi bi-people-fill"></i>
        <span class="sidebar-text">Clientes</span>
      </a>
      <a href="../modules/proveedores/readproveedor.php" class="nav-link">
        <i class="bi bi-truck"></i>
        <span class="sidebar-text">Proveedores</span>
      </a>
      <a href="../modules/productos/readproducto.php" class="nav-link">
        <i class="bi bi-box-seam"></i>
        <span class="sidebar-text">Productos</span>
      </a>
      <a href="../modules/ventas/readventas.php" class="nav-link">
        <i class="bi bi-cart-check"></i>
        <span class="sidebar-text">Ventas</span>
      </a>
      <a href="../modules/compras/readcompras.php" class="nav-link">
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
      <a href="../auth/logout.php" class="nav-link text-danger">
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
              <?php echo htmlspecialchars(explode(' ', $usuario['nombre'])[0], ENT_QUOTES, 'UTF-8'); ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">
                <i class="bi bi-person me-2"></i>Mi Perfil
              </a>
              <a class="dropdown-item" href="#">
                <i class="bi bi-gear me-2"></i>Configuración
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger" href="../auth/logout.php">
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
        <div class="col-12">
          <div class="card stat-card bg-primary text-white">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="card-title">¡Bienvenido de vuelta, <?php echo explode(' ', $usuario['nombre'])[0]; ?>! 👋</h3>
                  <p class="card-text">Aquí tienes un resumen de tu negocio hoy.</p>
                </div>
                <div class="col-md-4 text-end">
                  <i class="bi bi-graph-up-arrow display-4 opacity-50"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
          <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
              <i class="bi bi-people"></i>
            </div>
            <div class="stat-number text-primary">1,248</div>
            <div class="stat-title">Total Clientes</div>
            <div class="text-success small">
              <i class="bi bi-arrow-up"></i> 12% desde el mes pasado
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
          <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
              <i class="bi bi-cart-check"></i>
            </div>
            <div class="stat-number text-success">356</div>
            <div class="stat-title">Ventas del Mes</div>
            <div class="text-success small">
              <i class="bi bi-arrow-up"></i> 8% desde el mes pasado
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
          <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
              <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-number text-warning">$12,450</div>
            <div class="stat-title">Ingresos Totales</div>
            <div class="text-success small">
              <i class="bi bi-arrow-up"></i> 15% desde el mes pasado
            </div>
          </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
          <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
              <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-number text-info">2,847</div>
            <div class="stat-title">Productos en Stock</div>
            <div class="text-danger small">
              <i class="bi bi-arrow-down"></i> 3% desde el mes pasado
            </div>
          </div>
        </div>
      </div>

      <!-- Charts and Activity -->
      <div class="row g-4">
        <div class="col-xl-8">
          <div class="chart-container">
            <h5 class="mb-4">Ventas Mensuales</h5>
            <!-- Aquí iría un gráfico -->
            <div class="bg-light rounded p-5 text-center">
              <i class="bi bi-bar-chart display-1 text-muted"></i>
              <p class="text-muted mt-3">Gráfico de ventas mensuales</p>
              <small class="text-muted">(Integrar librería de gráficos como Chart.js aquí)</small>
            </div>
          </div>
        </div>
        
        <div class="col-xl-4">
          <div class="activity-container">
            <h5 class="mb-4">Actividad Reciente</h5>
            <div class="activity-list">
              <div class="activity-item">
                <div class="activity-icon bg-primary text-white">
                  <i class="bi bi-cart-plus"></i>
                </div>
                <div>
                  <h6 class="mb-1">Nueva venta registrada</h6>
                  <small class="text-muted">Hace 5 minutos</small>
                </div>
              </div>
              
              <div class="activity-item">
                <div class="activity-icon bg-success text-white">
                  <i class="bi bi-person-plus"></i>
                </div>
                <div>
                  <h6 class="mb-1">Cliente nuevo registrado</h6>
                  <small class="text-muted">Hace 1 hora</small>
                </div>
              </div>
              
              <div class="activity-item">
                <div class="activity-icon bg-warning text-white">
                  <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                  <h6 class="mb-1">Stock bajo en productos</h6>
                  <small class="text-muted">Hace 2 horas</small>
                </div>
              </div>
              
              <div class="activity-item">
                <div class="activity-icon bg-info text-white">
                  <i class="bi bi-truck"></i>
                </div>
                <div>
                  <h6 class="mb-1">Pedido de proveedor recibido</h6>
                  <small class="text-muted">Hace 3 horas</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="row g-4 mt-2">
        <div class="col-12">
          <div class="chart-container">
            <h5 class="mb-4">Acciones Rápidas</h5>
            <div class="row g-3">
              <div class="col-md-3 col-6">
                <a href="../modules/clientes/readcliente.php" class="btn btn-outline-primary w-100 py-3">
                  <i class="bi bi-people-fill display-6 d-block mb-2"></i>
                  Gestionar Clientes
                </a>
              </div>
              <div class="col-md-3 col-6">
                <a href="../modules/productos/readproducto.php" class="btn btn-outline-success w-100 py-3">
                  <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                  Gestionar Productos
                </a>
              </div>
              <div class="col-md-3 col-6">
                <a href="../modules/ventas/readventas.php" class="btn btn-outline-warning w-100 py-3">
                  <i class="bi bi-cart-check display-6 d-block mb-2"></i>
                  Ver Ventas
                </a>
              </div>
              <div class="col-md-3 col-6">
                <a href="#" class="btn btn-outline-info w-100 py-3">
                  <i class="bi bi-graph-up display-6 d-block mb-2"></i>
                  Ver Reportes
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-0">&copy; 2025 Sistema ERP. Todos los derechos reservados.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <span class="text-muted">v2.1.0</span>
          </div>
        </div>
      </div>
    </footer>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Sidebar Toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');
      
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');
    });

    // Active nav link
    document.addEventListener('DOMContentLoaded', function() {
      const currentPage = window.location.pathname.split('/').pop();
      const navLinks = document.querySelectorAll('.nav-link');
      
      navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
          link.classList.add('active');
        } else {
          link.classList.remove('active');
        }
      });
    });

    // Auto-update time
    function updateTime() {
      const now = new Date();
      document.getElementById('currentTime').textContent = now.toLocaleString('es-ES');
    }
    
    setInterval(updateTime, 1000);
    updateTime();
  </script>
</body>
</html>