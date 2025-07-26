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

// Registrar nuevo cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    if ($nombre && $apellido) {
        $stmt = $conn->prepare('INSERT INTO clientes (nombre, apellido, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $nombre, $apellido, $telefono, $correo, $direccion);
        if ($stmt->execute()) {
            $mensaje = 'Cliente registrado correctamente.';
        } else {
            $error = 'Error al registrar cliente.';
        }
        $stmt->close();
    } else {
        $error = 'El nombre y apellido son obligatorios.';
    }
}

// Eliminar cliente
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare('DELETE FROM clientes WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $mensaje = 'Cliente eliminado correctamente.';
    } else {
        $error = 'Error al eliminar cliente.';
    }
    $stmt->close();
}

// Editar cliente
$cliente_editar = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $conn->prepare('SELECT id, nombre, apellido, telefono, correo, direccion FROM clientes WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $cliente_editar = $res->fetch_assoc();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    if ($id && $nombre && $apellido) {
        $stmt = $conn->prepare('UPDATE clientes SET nombre=?, apellido=?, telefono=?, correo=?, direccion=? WHERE id=?');
        $stmt->bind_param('sssssi', $nombre, $apellido, $telefono, $correo, $direccion, $id);
        if ($stmt->execute()) {
            $mensaje = 'Cliente actualizado correctamente.';
        } else {
            $error = 'Error al actualizar cliente.';
        }
        $stmt->close();
    } else {
        $error = 'El nombre y apellido son obligatorios.';
    }
}

// Obtener lista de clientes
$clientes = [];
$result = $conn->query('SELECT id, nombre, apellido, telefono, correo, direccion, fecha_registro FROM clientes ORDER BY id ASC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../../templates/navbar.php'; ?>
    
    <div class="container py-4">
        <h2 class="mb-4 display-3">Gestión de Clientes <i class="bi bi-people-fill text-danger"></i></h2>
        <a href="../../dashboard.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Volver al dashboard</a>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"> <?= $mensaje ?> </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?= $error ?> </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header"><?php echo $cliente_editar ? 'Editar cliente' : 'Registrar nuevo cliente'; ?></div>
        <div class="card-body">
            <form method="post">
                <div class="row g-2">
                    <?php if ($cliente_editar): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($cliente_editar['id']) ?>">
                    <?php endif; ?>
                    <div class="col-md-2">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre" required value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['nombre']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="apellido" class="form-control" placeholder="Apellido" required value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['apellido']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="telefono" class="form-control" placeholder="Teléfono" value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['telefono']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="correo" class="form-control" placeholder="Correo electrónico" value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['correo']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="direccion" class="form-control" placeholder="Dirección" value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['direccion']) : '' ?>">
                    </div>
                    <div class="col-md-1">
                        <?php if ($cliente_editar): ?>
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
        <div class="card-header">Lista de clientes</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Fecha registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['id']) ?></td>
                        <td><?= htmlspecialchars($c['nombre']) ?></td>
                        <td><?= htmlspecialchars($c['apellido']) ?></td>
                        <td><?= htmlspecialchars($c['telefono']) ?></td>
                        <td><?= htmlspecialchars($c['correo']) ?></td>
                        <td><?= htmlspecialchars($c['direccion']) ?></td>
                        <td><?= htmlspecialchars($c['fecha_registro']) ?></td>
                        <td>
                            <a href="?eliminar=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este cliente?')">Eliminar</a>
                            <a href="?editar=<?= $c['id'] ?>" class="btn btn-sm btn-warning ms-1">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 