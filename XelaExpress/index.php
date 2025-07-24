<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XelaExpress - Punto de Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
    <div class="container text-center">
        <h1 class="display-3 fw-bold mb-4">XelaExpress</h1>
        <p class="lead mb-5">Tu punto de venta para consumo diario</p>
        <a href="login.php" class="btn btn-primary btn-lg">Iniciar Sesión</a>
    </div>
</body>
</html> 