<?php
// Detectar la ruta base automáticamente
$current_path = $_SERVER['REQUEST_URI'];
$is_module = strpos($current_path, '/modules/') !== false;
$base_path = $is_module ? '../../' : '';
$logo_path = $base_path . 'assets/img/Xela1.png';
$dashboard_path = $base_path . 'dashboard.php';
$logout_path = $base_path . 'logout.php';

// Detectar la página actual para marcar el menú activo
$current_page = basename($_SERVER['PHP_SELF']);
if ($is_module) {
    $current_module = basename(dirname($_SERVER['REQUEST_URI']));
} else {
    $current_module = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XelaExpress - Sistema de Punto de Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/sidebar.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="<?= $logo_path ?>" alt="Logo XelaExpress" class="sidebar-logo">
                <span class="sidebar-brand-text">XelaExpress</span>
            </div>
            <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <div class="sidebar-content">
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" href="<?= $dashboard_path ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_module == 'ventas') ? 'active' : '' ?>" href="<?= $base_path ?>modules/ventas/">
                            <i class="bi bi-cash-stack"></i>
                            <span>Ventas</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_module == 'productos') ? 'active' : '' ?>" href="<?= $base_path ?>modules/productos/">
                            <i class="bi bi-box-seam"></i>
                            <span>Productos</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_module == 'clientes') ? 'active' : '' ?>" href="<?= $base_path ?>modules/clientes/">
                            <i class="bi bi-people"></i>
                            <span>Clientes</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_module == 'usuarios') ? 'active' : '' ?>" href="<?= $base_path ?>modules/usuarios/">
                            <i class="bi bi-person-gear"></i>
                            <span>Usuarios</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="user-details">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario') ?></div>
                    <div class="user-role"><?= htmlspecialchars($_SESSION['rol'] ?? 'Usuario') ?></div>
                </div>
            </div>
            
            <div class="logout-section">
                <a href="<?= $logout_path ?>" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Overlay para móviles -->
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

    <!-- Contenido principal -->
    <div class="main-content" id="mainContent">
        <!-- Top bar -->
        <div class="top-bar">
            <div class="top-bar-left">
                <button class="sidebar-toggle-mobile d-lg-none" id="sidebarToggleMobile">
                    <i class="bi bi-list"></i>
                </button>
                <div class="page-title">
                    <h4 class="mb-0" id="pageTitle">
                        <?php
                        // Detectar el título basado en la página actual
                        if ($current_page == 'dashboard.php') {
                            echo 'Dashboard';
                        } elseif ($current_module == 'ventas') {
                            echo 'Gestión de Ventas';
                        } elseif ($current_module == 'productos') {
                            echo 'Gestión de Productos';
                        } elseif ($current_module == 'clientes') {
                            echo 'Gestión de Clientes';
                        } elseif ($current_module == 'usuarios') {
                            echo 'Gestión de Usuarios';
                        } else {
                            echo 'XelaExpress';
                        }
                        ?>
                    </h4>
                </div>
            </div>
            
            <div class="top-bar-right">
                <div class="user-menu-mobile d-lg-none">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= $dashboard_path ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $logout_path ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido de la página -->
        <div class="page-content">
