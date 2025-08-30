<?php
// Detectar la ruta base automáticamente
$current_path = $_SERVER['REQUEST_URI'];
$is_module = strpos($current_path, '/modules/') !== false;
$base_path = $is_module ? '../../' : '';
$logo_path = $base_path . 'assets/img/Xela1.png';
$dashboard_path = $base_path . 'dashboard.php';
$logout_path = $base_path . 'logout.php';
?>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $dashboard_path; ?>">
            <img src="<?php echo $logo_path; ?>" alt="Logo XelaExpress" class="navbar-logo">
            <span class="brand-text">XelaExpress</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <div class="navbar-nav ms-auto">
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle user-menu" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> 
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                        <?php if (isset($_SESSION['rol'])): ?>
                            <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($_SESSION['rol']); ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="<?php echo $dashboard_path; ?>"><i class="bi bi-house-door"></i> Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo $logout_path; ?>"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a class="nav-link" href="<?php echo $base_path; ?>login.php">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </a>
            <?php endif; ?>
            </div>
        </div>
    </div>
</nav> 