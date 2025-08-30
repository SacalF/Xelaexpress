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
    <title>Productos - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../../templates/navbar.php'; ?>
    
    <div class="container py-4">
        <h2 class="mb-4 display-3">Gestión de Productos <i class="bi bi-box-seam text-primary"></i></h2>
        <a href="../../dashboard.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver al dashboard</a>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"> <?= $mensaje ?> </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?= $error ?> </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header"><?php echo $producto_editar ? 'Editar producto' : 'Registrar nuevo producto'; ?></div>
        <div class="card-body">
            <form method="post">
                <div class="row g-2">
                    <?php if ($producto_editar): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($producto_editar['id']) ?>">
                    <?php endif; ?>
                    <div class="col-md-3">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre" required value="<?= $producto_editar ? htmlspecialchars($producto_editar['nombre']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="descripcion" class="form-control" placeholder="Descripción" value="<?= $producto_editar ? htmlspecialchars($producto_editar['descripcion']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="precio" class="form-control" placeholder="Precio" min="0.01" step="0.01" required value="<?= $producto_editar ? htmlspecialchars($producto_editar['precio']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="stock" class="form-control" placeholder="Stock" min="0" required value="<?= $producto_editar ? htmlspecialchars($producto_editar['stock']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <?php if ($producto_editar): ?>
                            <button type="submit" name="actualizar" class="btn btn-success w-100">Actualizar</button>
                            <a href="index.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                        <?php else: ?>
                            <button type="submit" name="registrar" class="btn btn-primary w-100">Registrar</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Lista de productos</div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Creado en</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['descripcion']) ?></td>
                        <td>Q <?= number_format($p['precio'], 2) ?></td>
                        <td><?= htmlspecialchars($p['stock']) ?></td>
                        <td><?= htmlspecialchars($p['creado_en']) ?></td>
                        <td>
                            <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                            <a href="?editar=<?= $p['id'] ?>" class="btn btn-sm btn-warning ms-1">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 