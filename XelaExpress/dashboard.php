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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a6cf7;
            --secondary: #6a11cb;
            --success: #00c9a7;
            --info: #00d2ff;
            --warning: #ff9a44;
            --danger: #ff3860;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
            color: white !important;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .stat-card {
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .stat-card:nth-child(1)::before { background: linear-gradient(90deg, var(--primary), var(--info)); }
        .stat-card:nth-child(2)::before { background: linear-gradient(90deg, var(--success), #00d2ff); }
        .stat-card:nth-child(3)::before { background: linear-gradient(90deg, var(--warning), #ff9a44); }
        .stat-card:nth-child(4)::before { background: linear-gradient(90deg, #ff3860, #ff758c); }
        
        .display-6 {
            font-weight: 700;
            color: var(--dark);
            margin-top: 0.5rem;
        }
        
        .btn-module {
            background: white;
            border: none;
            border-radius: 12px;
            padding: 1.5rem 1rem;
            font-weight: 600;
            color: var(--dark);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .btn-module:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(74, 108, 247, 0.3);
        }
        
        .btn-module i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-module:hover i {
            transform: scale(1.1);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 16px;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            color: white;
            box-shadow: 0 10px 25px rgba(74, 108, 247, 0.25);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            z-index: 1;
        }
        
        .welcome-section h1 {
            position: relative;
            z-index: 2;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome-section p {
            position: relative;
            z-index: 2;
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        .user-greeting {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .user-greeting i {
            margin-right: 0.5rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container"> 
            <img src = "assets/img/xela1.png" alt = "Logo XelaExpress" class="navbar-brand fw-bold" style="max-width: 150px; margin-bottom: 1rem;">
            <h1 class="text-white text-center">XelaExpress</h1>
            <div class="d-flex">
                <span class="me-3 text-white d-flex align-items-center">
                    <span class="user-greeting">
                        <i class="fas fa-user-circle"></i>
                        Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                    </span>
                </span>
                <a href="logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt me-1"></i> Salir
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container py-4">
        <div class="welcome-section text-center mb-5">
            <h1 class="mb-3">Bienvenido a XelaExpress</h1>
            
            <p class="mb-0">Tu panel de control para gestionar ventas, productos, usuarios y clientes de manera eficiente</p>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-3 mb-3 fade-in delay-1">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-boxes text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted">Productos</h5>
                        <p class="display-6 fw-bold mb-0"><?php echo $total_productos; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 fade-in delay-2">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-shopping-cart text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted">Ventas</h5>
                        <p class="display-6 fw-bold mb-0"><?php echo $total_ventas; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 fade-in delay-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-chart-line text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted">Ingresos</h5>
                        <p class="display-6 fw-bold mb-0">Q <?php echo number_format($total_ingresos, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 fade-in delay-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-users text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title text-muted">Clientes</h5>
                        <p class="display-6 fw-bold mb-0"><?php echo $total_clientes; ?></p>
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
</body>
</html>