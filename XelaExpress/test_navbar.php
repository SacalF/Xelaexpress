<?php
session_start();
// Simular un usuario logueado para pruebas
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario'] = 'admin_test';
$_SESSION['rol'] = 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Navbar - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/navbar.php'; ?>
    
    <div class="container py-4">
        <h1>Test de Navbar</h1>
        <p>Esta es una página de prueba para verificar que la navbar funciona correctamente.</p>
        <p>Usuario: <?php echo $_SESSION['usuario']; ?></p>
        <p>Rol: <?php echo $_SESSION['rol']; ?></p>
        
        <div class="alert alert-info">
            <h4>Verificaciones:</h4>
            <ul>
                <li>✅ Logo debe aparecer (Xela1.png)</li>
                <li>✅ Nombre "XelaExpress" debe estar centrado</li>
                <li>✅ Usuario debe aparecer con icono y badge de rol</li>
                <li>✅ Dropdown debe funcionar</li>
                <li>✅ Botón "Cerrar Sesión" debe funcionar</li>
            </ul>
        </div>
        
        <div class="alert alert-warning">
            <h4>Debug Info:</h4>
            <p><strong>Ruta actual:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
            <p><strong>Es módulo:</strong> <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/') !== false ? 'Sí' : 'No'; ?></p>
            <p><strong>Ruta base:</strong> <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/') !== false ? '../../' : ''; ?></p>
            <p><strong>Ruta del logo:</strong> <?php echo (strpos($_SERVER['REQUEST_URI'], '/modules/') !== false ? '../../' : '') . 'assets/img/Xela1.png'; ?></p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 