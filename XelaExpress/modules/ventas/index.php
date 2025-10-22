<?php
session_start();
require_once '../../config/db.php';

// Proteger solo para usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../login.php');
    exit();
}

$mensaje = '';
$error = '';

// Obtener productos para el formulario
$productos = [];
$res = $conn->query('SELECT id, nombre, precio, stock FROM productos WHERE stock > 0 ORDER BY nombre ASC');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $productos[] = $row;
    }
}

// Obtener clientes para el formulario (incluyendo apellido)
$clientes = [];
$res_clientes = $conn->query('SELECT id, nombre, apellido FROM clientes ORDER BY nombre ASC, apellido ASC');
if ($res_clientes) {
    while ($row_cliente = $res_clientes->fetch_assoc()) {
        $clientes[] = $row_cliente;
    }
}

// Registrar nueva venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_venta'])) {
    $items = $_POST['items'] ?? [];
    $total = 0;
    $detalles = [];
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : null;

    if (empty($items)) {
        $error = 'Debes agregar al menos un producto.';
    } else {
        foreach ($items as $item) {
            $producto_id = intval($item['producto_id']);
            $cantidad = intval($item['cantidad']);
            if ($producto_id && $cantidad > 0) {
                // Obtener precio actual y stock
                $stmt = $conn->prepare('SELECT precio, stock FROM productos WHERE id = ?');
                $stmt->bind_param('i', $producto_id);
                $stmt->execute();
                $stmt->bind_result($precio, $stock);
                if ($stmt->fetch()) {
                    if ($cantidad > $stock) {
                        $error = 'Stock insuficiente para uno de los productos.';
                        break;
                    }
                    $total += $precio * $cantidad;
                    $detalles[] = [
                        'producto_id' => $producto_id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precio
                    ];
                }
                $stmt->close();
            }
        }
    }

    if (!$error && $total > 0 && count($detalles) > 0) {
        // Registrar venta
        $stmt = $conn->prepare('INSERT INTO ventas (usuario_id, cliente_id, total) VALUES (?, ?, ?)');
        $stmt->bind_param('iid', $_SESSION['usuario_id'], $cliente_id, $total);
        if ($stmt->execute()) {
            $venta_id = $stmt->insert_id;
            // Insertar detalles y actualizar stock
            foreach ($detalles as $d) {
                $stmt2 = $conn->prepare('INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
                $stmt2->bind_param('iiid', $venta_id, $d['producto_id'], $d['cantidad'], $d['precio_unitario']);
                $stmt2->execute();
                $stmt2->close();
                // Actualizar stock
                $stmt3 = $conn->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');
                $stmt3->bind_param('ii', $d['cantidad'], $d['producto_id']);
                $stmt3->execute();
                $stmt3->close();
            }
            $mensaje = 'Venta registrada correctamente.';
        } else {
            $error = 'Error al registrar la venta: ' . $stmt->error;
        }
        $stmt->close();
    } elseif (!$error) {
        $error = 'Debes agregar al menos un producto válido.';
    }
}

// Obtener lista de ventas
$ventas = [];
$res = $conn->query('SELECT v.id, v.fecha, v.total, u.usuario, c.nombre AS nombre_cliente, c.apellido AS apellido_cliente
                     FROM ventas v
                     JOIN usuarios u ON v.usuario_id = u.id
                     LEFT JOIN clientes c ON v.cliente_id = c.id
                     ORDER BY v.id DESC');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $ventas[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script>
    let productIndex = 0;

    function agregarProducto() {
        const productosLista = document.getElementById('productos-lista');
        const originalRow = document.querySelector('.producto-row');
        const newRow = originalRow.cloneNode(true);

        productIndex++;
        newRow.querySelectorAll('select, input').forEach(input => {
            input.value = '';
            const oldName = input.name;
            if (oldName) {
                input.name = oldName.replace(/\[\d+\]/, `[${productIndex}]`);
            }
        });
        
        // Actualizar el subtotal display
        newRow.querySelector('.subtotal-display').textContent = 'Q 0.00';
        
        productosLista.appendChild(newRow);
        actualizarTotales();
    }

    function quitarProducto(btn) {
        const rows = document.querySelectorAll('.producto-row');
        if (rows.length > 1) {
            btn.closest('.producto-row').remove();
            actualizarTotales();
        }
    }

    function actualizarTotales() {
        let totalProductos = 0;
        let totalVenta = 0;
        
        document.querySelectorAll('.producto-row').forEach(row => {
            const cantidadInput = row.querySelector('.cantidad-input');
            const productoSelect = row.querySelector('.producto-select');
            const subtotalDisplay = row.querySelector('.subtotal-display');
            
            if (productoSelect.value && cantidadInput.value) {
                const precio = parseFloat(productoSelect.selectedOptions[0].dataset.precio) || 0;
                const cantidad = parseInt(cantidadInput.value) || 0;
                const subtotal = precio * cantidad;
                
                subtotalDisplay.textContent = `Q ${subtotal.toFixed(2)}`;
                totalProductos += cantidad;
                totalVenta += subtotal;
            } else {
                subtotalDisplay.textContent = 'Q 0.00';
            }
        });
        
        document.getElementById('total-productos').textContent = totalProductos;
        document.getElementById('total-venta').textContent = `Q ${totalVenta.toFixed(2)}`;
    }

    // Event listeners para actualizar totales
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('producto-select') || e.target.classList.contains('cantidad-input')) {
            actualizarTotales();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            actualizarTotales();
        }
    });

    // Validación de formularios Bootstrap
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // Prevenir entrada de caracteres no numéricos en cantidad
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            // Solo permitir números enteros
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            
            const cantidad = parseInt(e.target.value) || 0;
            const productoSelect = e.target.closest('.producto-row').querySelector('.producto-select');
            
            if (productoSelect.value) {
                const stock = parseInt(productoSelect.selectedOptions[0].dataset.stock) || 0;
                
                if (cantidad > stock) {
                    e.target.setCustomValidity(`Stock insuficiente. Disponible: ${stock}`);
                    e.target.classList.add('is-invalid');
                } else if (cantidad < 1) {
                    e.target.setCustomValidity('La cantidad debe ser mayor a 0');
                    e.target.classList.add('is-invalid');
                } else {
                    e.target.setCustomValidity('');
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                }
            }
        }
    });

    // Prevenir entrada de 'e', '+', '-' en campos de cantidad
    document.addEventListener('keydown', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            if (e.key === 'e' || e.key === 'E' || e.key === '+' || e.key === '-') {
                e.preventDefault();
            }
        }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    </script>
</head>
<body>
    <?php include '../../templates/sidebar.php'; ?>
    
    <div class="container-fluid">
        <div class="d-flex flex-column mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h2 class="h3 mb-0">Gestión de Ventas</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="exportarVentas()">
                        <i class="bi bi-download me-1"></i>Exportar
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="mostrarFormulario()">
                        <i class="bi bi-plus-lg me-1"></i>Nueva Venta
                    </button>
                </div>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-success py-2 alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-cart-plus me-2"></i>
            Registrar nueva venta
        </div>
        <div class="card-body">
            <form method="post" class="needs-validation" novalidate>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="cliente_id" class="form-label">
                            <i class="bi bi-person me-1"></i>Cliente
                        </label>
                        <select name="cliente_id" id="cliente_id" class="form-select">
                            <option value="">Selecciona un cliente (Opcional)</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['nombre']) ?> <?= htmlspecialchars($c['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted">
                            <small>Seleccione un cliente registrado (opcional)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-calendar me-1"></i>Fecha de venta
                        </label>
                        <input type="datetime-local" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" readonly>
                        <div class="form-text text-muted">
                            <small>Fecha y hora automática</small>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>Productos para la venta
                    </h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarProducto()">
                        <i class="bi bi-plus-lg me-1"></i>Agregar producto
                    </button>
                </div>
                
                <div id="productos-lista">
                    <div class="row g-3 align-items-end producto-row mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Producto <span class="text-danger">*</span></label>
                            <select name="items[0][producto_id]" class="form-select producto-select" required>
                                <option value="">Selecciona producto</option>
                                <?php foreach ($productos as $p): ?>
                                    <option value="<?= $p['id'] ?>" 
                                            data-precio="<?= $p['precio'] ?>" 
                                            data-stock="<?= $p['stock'] ?>">
                                        <?= htmlspecialchars($p['nombre']) ?> - Q<?= number_format($p['precio'],2) ?> (Stock: <?= $p['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">
                                <small>Seleccione un producto disponible</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="items[0][cantidad]" 
                                   class="form-control cantidad-input" 
                                   placeholder="Ej: 2" 
                                   pattern="^\d+$"
                                   title="Solo números enteros"
                                   required>
                            <div class="form-text text-muted">
                                <small>Cantidad a vender</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Subtotal</label>
                            <div class="form-control-plaintext subtotal-display fw-bold text-success">
                                Q 0.00
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="btn-group-vertical w-100" role="group">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarProducto()" title="Agregar producto">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarProducto(this)" title="Quitar producto">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Total de productos:</strong> <span id="total-productos">0</span> | 
                            <strong>Total a pagar:</strong> <span id="total-venta" class="fw-bold text-success">Q 0.00</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="submit" name="registrar_venta" class="btn btn-primary btn-lg">
                            <i class="bi bi-cart-check me-2"></i>Registrar venta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Lista de ventas</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Cliente</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['id']) ?></td>
                        <td><?= htmlspecialchars($v['fecha']) ?></td>
                        <td><?= htmlspecialchars($v['usuario']) ?></td>
                        <td>
                            <?php
                            if (!empty($v['nombre_cliente']) || !empty($v['apellido_cliente'])) {
                                echo htmlspecialchars(trim($v['nombre_cliente'] . ' ' . $v['apellido_cliente']));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td>Q <?= number_format($v['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/sidebar.js"></script>
<script>
    // Inicializar sidebar
    document.addEventListener('DOMContentLoaded', function() {
        if (window.sidebarManager) {
            window.sidebarManager.updatePageTitle();
        }
    });

    // Función para mostrar formulario
    function mostrarFormulario() {
        const formulario = document.querySelector('.card');
        if (formulario) {
            formulario.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Función para exportar ventas
    function exportarVentas() {
        const tabla = document.querySelector('.table');
        if (tabla) {
            const csv = tablaToCSV(tabla);
            downloadCSV(csv, 'ventas.csv');
        }
    }

    function tablaToCSV(tabla) {
        let csv = [];
        const filas = tabla.querySelectorAll('tr');
        
        filas.forEach(fila => {
            const celdas = fila.querySelectorAll('td, th');
            const filaCSV = Array.from(celdas).map(celda => {
                return '"' + celda.textContent.replace(/"/g, '""') + '"';
            }).join(',');
            csv.push(filaCSV);
        });
        
        return csv.join('\n');
    }

    function downloadCSV(csv, filename) {
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
</body>
</html>

