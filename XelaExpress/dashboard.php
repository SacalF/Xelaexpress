<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'config/db.php';

// Consultas para los reportes
// Total de productos
$res = $conn->query('SELECT COUNT(*) AS total FROM productos');
$total_productos = $res ? $res->fetch_assoc()['total'] : 0;
// Total de ventas
$res = $conn->query('SELECT COUNT(*) AS total FROM ventas');
$total_ventas = $res ? $res->fetch_assoc()['total'] : 0;
// Total de ingresos
$res = $conn->query('SELECT SUM(total) AS ingresos FROM ventas');
$total_ingresos = $res ? ($res->fetch_assoc()['ingresos'] ?? 0) : 0;
// Total de clientes
$res = $conn->query('SELECT COUNT(*) AS total FROM clientes');
$total_clientes = $res ? $res->fetch_assoc()['total'] : 0;

// Ventas por mes (últimos 6 meses)
$ventas_mes = [];
$res = $conn->query("SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total FROM ventas GROUP BY mes ORDER BY mes DESC LIMIT 6");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $ventas_mes[$row['mes']] = $row['total'];
    }
}
$ventas_mes = array_reverse($ventas_mes, true); // Para mostrar de más antiguo a más reciente

// Productos más vendidos (top 5)
$productos_vendidos = [];
$res = $conn->query("SELECT p.nombre, SUM(dv.cantidad) as total FROM detalle_ventas dv JOIN productos p ON dv.producto_id = p.id GROUP BY dv.producto_id ORDER BY total DESC LIMIT 5");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $productos_vendidos[$row['nombre']] = $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">XelaExpress</a>
            <div class="d-flex">
                <span class="me-3">Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <a href="logout.php" class="btn btn-outline-danger">Salir</a>
            </div>
        </div>
    </nav>
    <div class="container text-center">
        <h1 class="mb-4">Bienvenido a XelaExpress</h1>
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Productos</h5>
                        <p class="display-6 fw-bold mb-0"><?php echo $total_productos; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Ventas</h5>
                        <p class="display-6 fw-bold mb-0"><?php echo $total_ventas; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Ingresos</h5>
                        <p class="display-6 fw-bold mb-0">Q <?php echo number_format($total_ingresos, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Clientes</h5>
                        <p class="display-6 fw-bold mb-0"><?php echo $total_clientes; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-3 mb-3">
                <a href="modules/ventas/" class="btn btn-primary w-100 py-3">Ventas</a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="modules/productos/" class="btn btn-primary w-100 py-3">Productos</a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="modules/usuarios/" class="btn btn-primary w-100 py-3">Usuarios</a>
            </div>
        </div>
    </div>
    <!-- Gráficas -->
    <div class="container mb-5">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">Ventas por mes (últimos 6 meses)</div>
                    <div class="card-body">
                        <canvas id="ventasMesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">Top 5 productos más vendidos</div>
                    <div class="card-body">
                        <canvas id="productosVendidosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Ventas por mes
    const ventasMesLabels = <?php echo json_encode(array_keys($ventas_mes)); ?>;
    const ventasMesData = <?php echo json_encode(array_values($ventas_mes)); ?>;
    new Chart(document.getElementById('ventasMesChart'), {
        type: 'bar',
        data: {
            labels: ventasMesLabels,
            datasets: [{
                label: 'Ventas',
                data: ventasMesData,
                backgroundColor: '#4f8cff',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
    // Productos más vendidos
    const productosLabels = <?php echo json_encode(array_keys($productos_vendidos)); ?>;
    const productosData = <?php echo json_encode(array_values($productos_vendidos)); ?>;
    new Chart(document.getElementById('productosVendidosChart'), {
        type: 'pie',
        data: {
            labels: productosLabels,
            datasets: [{
                label: 'Cantidad vendida',
                data: productosData,
                backgroundColor: [
                    '#4f8cff', '#2563eb', '#60a5fa', '#93c5fd', '#1e40af'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
    </script>
</body>
</html> 