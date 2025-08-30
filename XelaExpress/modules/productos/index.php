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

// Registrar nuevo producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    
    if ($nombre && $precio > 0 && $stock >= 0) {
        $stmt = $conn->prepare('INSERT INTO productos (nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssdi', $nombre, $descripcion, $precio, $stock);
        if ($stmt->execute()) {
            $mensaje = 'Producto registrado correctamente.';
        } else {
            $error = 'Error al registrar producto.';
        }
        $stmt->close();
    } else {
        $error = 'Todos los campos obligatorios deben estar completos y válidos.';
    }
}

// Eliminar producto
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare('DELETE FROM productos WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $mensaje = 'Producto eliminado correctamente.';
    } else {
        $error = 'Error al eliminar producto.';
    }
    $stmt->close();
}

// Editar producto
$producto_editar = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $conn->prepare('SELECT id, nombre, descripcion, precio, stock FROM productos WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $producto_editar = $res->fetch_assoc();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    
    if ($id && $nombre && $precio > 0 && $stock >= 0) {
        $stmt = $conn->prepare('UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=? WHERE id=?');
        $stmt->bind_param('ssdii', $nombre, $descripcion, $precio, $stock, $id);
        if ($stmt->execute()) {
            $mensaje = 'Producto actualizado correctamente.';
            $producto_editar = null; // Limpiar el formulario de edición
        } else {
            $error = 'Error al actualizar producto.';
        }
        $stmt->close();
    } else {
        $error = 'Todos los campos obligatorios deben estar completos y válidos.';
    }
}

// Obtener lista de productos
$productos = [];
$result = $conn->query('SELECT id, nombre, descripcion, precio, stock, creado_en FROM productos ORDER BY id ASC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/table-styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../../templates/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex flex-column mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h2 class="h3 mb-0">Gestión de Productos</h2>
                <a href="../../dashboard.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i><span class="d-none d-md-inline"> Volver</span>
                </a>
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

        <!-- Formulario de registro/edición -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-plus-circle me-2"></i>
                <?php echo $producto_editar ? 'Editar producto' : 'Nuevo producto'; ?>
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <?php if ($producto_editar): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($producto_editar['id']) ?>">
                    <?php endif; ?>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nombre del producto <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" required 
                                   value="<?= $producto_editar ? htmlspecialchars($producto_editar['nombre']) : '' ?>"
                                   placeholder="Ingrese el nombre del producto">
                            <div class="invalid-feedback">
                                Por favor ingrese el nombre del producto.
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="descripcion" class="form-control"
                                   value="<?= $producto_editar ? htmlspecialchars($producto_editar['descripcion']) : '' ?>"
                                   placeholder="Descripción del producto (opcional)">
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Precio (Q) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Q</span>
                                <input type="number" name="precio" class="form-control" min="0.01" step="0.01" required 
                                       value="<?= $producto_editar ? htmlspecialchars($producto_editar['precio']) : '' ?>"
                                       placeholder="0.00">
                                <div class="invalid-feedback">
                                    Por favor ingrese un precio válido.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <label class="form-label">Stock <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" min="0" required 
                                   value="<?= $producto_editar ? htmlspecialchars($producto_editar['stock']) : '' ?>"
                                   placeholder="Cantidad en stock">
                            <div class="invalid-feedback">
                                Por favor ingrese la cantidad en stock.
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <?php if ($producto_editar): ?>
                            <div class="d-flex gap-2 flex-column flex-md-row">
                                <button type="submit" name="actualizar" class="btn btn-success flex-grow-1">
                                    <i class="bi bi-check-lg"></i> Actualizar producto
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                            </div>
                        <?php else: ?>
                            <button type="submit" name="registrar" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg"></i> Registrar producto
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de productos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <span><i class="bi bi-list-ul me-2"></i>Lista de productos</span>
                <span class="badge bg-primary"><?= count($productos) ?> productos</span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($productos)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="d-none d-md-table-cell">ID</th>
                                    <th>Producto</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($p['id']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></div>
                                        <?php if (!empty($p['descripcion'])): ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($p['descripcion']) ?></small>
                                        <?php endif; ?>
                                        <small class="text-muted d-block d-md-none">
                                            <i class="bi bi-hash"></i><?= htmlspecialchars($p['id']) ?>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">Q <?= number_format($p['precio'], 2) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['stock'] <= 5): ?>
                                            <span class="badge bg-danger"><?= $p['stock'] ?></span>
                                        <?php elseif ($p['stock'] <= 10): ?>
                                            <span class="badge bg-warning text-dark"><?= $p['stock'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?= $p['stock'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?editar=<?= $p['id'] ?>" class="btn btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                                <span class="d-none d-lg-inline"> Editar</span>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="confirmarEliminacion(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>')"
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                                <span class="d-none d-lg-inline"> Eliminar</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No hay productos registrados</h5>
                        <p class="text-muted">Comience agregando su primer producto usando el formulario de arriba.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para confirmar eliminación
        function confirmarEliminacion(id, nombre) {
            if (confirm(`¿Está seguro de que desea eliminar el producto "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                window.location.href = `?eliminar=${id}`;
            }
        }

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

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>