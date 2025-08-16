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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="welcome-section text-center mb-4 py-3">
            <div class="mb-2">
                <i class="bi bi-speedometer2" style="font-size: 2rem;"></i>
            </div>
            <h2 class="mb-2 h3">Panel de Control</h2>
            <p class="text-light mb-0 small">Sistema de gestión XelaExpress</p>
        </div>

        <div class="row mb-4 g-4">
            <div class="col-md-3 mb-3 fade-in delay-1">
                <div class="card stat-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-primary">Productos Registrados</span>
                        <i class="fas fa-boxes text-primary"></i>
                    </div>
                    <div class="card-body text-center py-3">
                        <h3 class="h2 fw-bold text-primary mb-0"><?php echo $total_productos; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 fade-in delay-2">
                <div class="card stat-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-success">Ventas Realizadas</span>
                        <i class="fas fa-shopping-cart text-success"></i>
                    </div>
                    <div class="card-body text-center py-3">
                        <h3 class="h2 fw-bold text-success mb-0"><?php echo $total_ventas; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 fade-in delay-3">
                <div class="card stat-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-warning">Ingresos Totales</span>
                        <i class="fas fa-chart-line text-warning"></i>
                    </div>
                    <div class="card-body text-center py-3">
                        <h3 class="h2 fw-bold text-warning mb-0">Q <?php echo number_format($total_ingresos, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 fade-in delay-4">
                <div class="card stat-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-danger">Clientes Registrados</span>
                        <i class="fas fa-users text-danger"></i>
                    </div>
                    <div class="card-body text-center py-3">
                        <h3 class="h2 fw-bold text-danger mb-0"><?php echo $total_clientes; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center mb-5">
            <div class="col-md-3 col-sm-6 mb-4 fade-in">
                <a href="modules/ventas/" class="btn btn-module">
                    <i class="fas fa-cash-register"></i>
                    Ventas
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-4 fade-in delay-1">
                <a href="modules/productos/" class="btn btn-module">
                    <i class="fas fa-box-open"></i>
                    Productos
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-4 fade-in delay-2">
                <a href="modules/usuarios/" class="btn btn-module">
                    <i class="fas fa-user-cog"></i>
                    Usuarios
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-4 fade-in delay-3">
                <a href="modules/clientes/" class="btn btn-module">
                    <i class="fas fa-address-book"></i>
                    Clientes
                </a>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-lg-6 mb-4 fade-in">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-chart-bar me-2"></i> Ventas por mes (últimos 6 meses)
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ventasMesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4 fade-in delay-1">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-star me-2"></i> Top 5 productos más vendidos
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="productosVendidosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container text-center">
            <p class="mb-0 text-muted">© 2025 XelaExpress. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Tu función para generar degradados que funciona perfectamente.
    function createGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // --- Gráfica de Ventas por mes ---
        const ventasMesLabels = <?php echo json_encode(array_keys($ventas_mes)); ?>;
        const ventasMesData = <?php echo json_encode(array_values($ventas_mes)); ?>;
        
        const ctxBar = document.getElementById('ventasMesChart').getContext('2d');
        if (ctxBar) { // Verificar si el elemento existe
            const barGradient = createGradient(ctxBar, 'rgba(106, 17, 203, 0.8)', 'rgba(74, 108, 247, 0.4)');
            
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ventasMesLabels,
                    datasets: [{
                        label: 'Ventas',
                        data: ventasMesData,
                        backgroundColor: barGradient,
                        borderColor: 'rgba(106, 17, 203, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.9)',
                            padding: 12,
                            titleFont: { size: 14 },
                            bodyFont: { size: 14 },
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { color: '#64748b' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b' }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
        
        // --- Gráfica de Productos más vendidos ---
        const productosLabels = <?php echo json_encode(array_keys($productos_vendidos)); ?>;
        const productosData = <?php echo json_encode(array_values($productos_vendidos)); ?>;
        
        const ctxPie = document.getElementById('productosVendidosChart').getContext('2d');
        if (ctxPie) { // Verificar si el elemento existe
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: productosLabels,
                    datasets: [{
                        label: 'Cantidad vendida',
                        data: productosData,
                        backgroundColor: [
                            createGradient(ctxPie, '#4a6cf7', '#6a11cb'),
                            createGradient(ctxPie, '#00c9a7', '#00d2ff'),
                            createGradient(ctxPie, '#ff9a44', '#ffb224'),
                            createGradient(ctxPie, '#ff3860', '#ff758c'),
                            createGradient(ctxPie, '#a855f7', '#d946ef')
                        ],
                        borderColor: 'rgba(255, 255, 255, 0.3)',
                        borderWidth: 2,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.9)',
                            padding: 12,
                            titleFont: { size: 14 },
                            bodyFont: { size: 14 }
                        }
                    },
                    cutout: '65%',
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>