<?php
session_start();
require_once '../../config/db.php';

// Proteger solo para administradores
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Mensaje de error o éxito
$mensaje = '';
$error = '';

// Registrar nuevo usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario';

    if ($usuario && $password && in_array($rol, ['admin', 'usuario'])) {
        // Verificar si el usuario ya existe
        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'El usuario ya existe.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare('INSERT INTO usuarios (usuario, password, rol) VALUES (?, ?, ?)');
            $stmt2->bind_param('sss', $usuario, $hash, $rol);
            if ($stmt2->execute()) {
                $mensaje = 'Usuario registrado correctamente.';
            } else {
                $error = 'Error al registrar usuario.';
            }
            $stmt2->close();
        }
        $stmt->close();
    } else {
        $error = 'Todos los campos son obligatorios.';
    }
}

// Eliminar usuario
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    // No permitir que un admin se elimine a sí mismo
    if ($id == $_SESSION['usuario_id']) {
        $error = 'No puedes eliminar tu propio usuario.';
    } else {
        $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $mensaje = 'Usuario eliminado correctamente.';
        } else {
            $error = 'Error al eliminar usuario.';
        }
        $stmt->close();
    }
}

// Obtener lista de usuarios
$usuarios = [];
$result = $conn->query('SELECT id, usuario, rol, creado_en FROM usuarios ORDER BY id ASC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../../templates/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex flex-column mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h3 mb-0">Gestión de Usuarios</h2>
                <a href="../../dashboard.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i><span class="d-none d-md-inline"> Volver</span>
                </a>
            </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"> <?= $mensaje ?> </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?= $error ?> </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Registrar nuevo usuario</div>
        <div class="card-body">
            <form method="post">
                <div class="row g-2">
                    <div class="col-12 col-md-3">
                        <input type="text" name="usuario" class="form-control" placeholder="Usuario" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                    </div>
                    <div class="col-12 col-md-2">
                        <select name="rol" class="form-select" required>
                            <option value="usuario">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-1">
                        <button type="submit" name="registrar" class="btn btn-primary w-100">Registrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Lista de usuarios</div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="d-none d-md-table-cell">ID</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th class="d-none d-lg-table-cell">Creado en</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['usuario']) ?></td>
                        <td><span class="badge bg-<?= $u['rol'] === 'admin' ? 'primary' : 'secondary' ?>"><?= htmlspecialchars($u['rol']) ?></span></td>
                        <td class="d-none d-lg-table-cell"><?= htmlspecialchars($u['creado_en']) ?></td>
                        <td class="text-end">
                            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                <div class="btn-group btn-group-sm">
                                    <a href="?eliminar=<?= $u['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar este usuario?')"><i class="bi bi-trash"></i></a>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-info">Usuario actual</span>
                            <?php endif; ?>
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