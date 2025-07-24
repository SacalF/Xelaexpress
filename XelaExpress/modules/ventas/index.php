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

// **NUEVO: Obtener clientes para el formulario**
$clientes = [];
$res_clientes = $conn->query('SELECT id, nombre FROM clientes ORDER BY nombre ASC');
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
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : null; // **NUEVO: Obtener cliente_id**

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
        // **MODIFICADO: Se agrega cliente_id a la inserción de ventas**
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
            $error = 'Error al registrar la venta: ' . $stmt->error; // Añadir el error para depuración
        }
        $stmt->close();
    } elseif (!$error) { // This condition was only hit if $error was not set
        $error = 'Debes agregar al menos un producto válido.'; // This message applies if total is 0 or no valid products added
    }
}

// Obtener lista de ventas
// **MODIFICADO: Se incluye el nombre del cliente en la lista de ventas**
$ventas = [];
$res = $conn->query('SELECT v.id, v.fecha, v.total, u.usuario, c.nombre AS nombre_cliente
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
    <title>Ventas - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    // Script para agregar/quitar productos dinámicamente
    function agregarProducto() {
        const productosLista = document.getElementById('productos-lista');
        const originalRow = document.querySelector('.producto-row');
        const newRow = originalRow.cloneNode(true);

        // Clear values and update names for new row
        const index = productosLista.children.length; // Get current number of product rows
        newRow.querySelectorAll('select, input').forEach(input => {
            input.value = '';
            // Update name attributes for unique indexing
            const oldName = input.name;
            if (oldName) {
                input.name = oldName.replace(/\[\d+\]/, `[${index}]`);
            }
        });
        productosLista.appendChild(newRow);
    }

    function quitarProducto(btn) {
        const rows = document.querySelectorAll('.producto-row');
        if (rows.length > 1) {
            btn.closest('.producto-row').remove();
        }
    }
    </script>
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Gestión de Ventas</h2>
    <a href="../../dashboard.php" class="btn btn-secondary mb-3">Volver al dashboard</a>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"> <?= $mensaje ?> </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?= $error ?> </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Registrar nueva venta</div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Cliente</label>
                    <select name="cliente_id" id="cliente_id" class="form-select">
                        <option value="">Selecciona un cliente (Opcional)</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= htmlspecialchars($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr>
                <h5 class="mb-3">Productos para la venta</h5>
                <div id="productos-lista">
                    <div class="row g-2 align-items-end producto-row mb-2">
                        <div class="col-md-5">
                            <select name="items[0][producto_id]" class="form-select" required>
                                <option value="">Selecciona producto</option>
                                <?php foreach ($productos as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['nombre']) ?> (Q<?= number_format($p['precio'],2) ?>, Stock: <?= $p['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="items[0][cantidad]" class="form-control" placeholder="Cantidad" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-success" onclick="agregarProducto()">+</button>
                            <button type="button" class="btn btn-danger" onclick="quitarProducto(this)">-</button>
                        </div>
                    </div>
                </div>
                <button type="submit" name="registrar_venta" class="btn btn-primary mt-3">Registrar venta</button>
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
                        <th>Cliente</th> <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['id']) ?></td>
                        <td><?= htmlspecialchars($v['fecha']) ?></td>
                        <td><?= htmlspecialchars($v['usuario']) ?></td>
                        <td><?= htmlspecialchars($v['nombre_cliente'] ?? 'N/A') ?></td> <td>Q <?= number_format($v['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>