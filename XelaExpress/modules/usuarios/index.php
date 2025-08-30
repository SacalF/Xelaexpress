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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - XelaExpress</title>
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
                <h2 class="h3 mb-0">Gestión de Usuarios</h2>
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

        <!-- Formulario de registro -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person-plus me-2"></i>Registrar nuevo usuario
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" name="usuario" class="form-control" placeholder="Nombre de usuario" required>
                            <div class="invalid-feedback">
                                Por favor ingrese un nombre de usuario.
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Contraseña" required minlength="6">
                            <div class="invalid-feedback">
                                La contraseña debe tener al menos 6 caracteres.
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                            <select name="rol" class="form-select" required>
                                <option value="">Seleccionar rol</option>
                                <option value="usuario">Usuario</option>
                                <option value="admin">Administrador</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un rol.
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" name="registrar" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Registrar usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de usuarios -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <span><i class="bi bi-people me-2"></i>Lista de usuarios</span>
                <span class="badge bg-primary"><?= count($usuarios) ?> usuarios</span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($usuarios)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
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
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($u['id']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($u['usuario']) ?></div>
                                        <small class="text-muted d-block d-md-none">
                                            <i class="bi bi-hash"></i><?= htmlspecialchars($u['id']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $u['rol'] === 'admin' ? 'primary' : 'secondary' ?>">
                                            <i class="bi bi-<?= $u['rol'] === 'admin' ? 'shield-check' : 'person' ?>"></i>
                                            <?= htmlspecialchars($u['rol']) ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($u['creado_en'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="confirmarEliminacion(<?= $u['id'] ?>, '<?= htmlspecialchars($u['usuario'], ENT_QUOTES) ?>')"
                                                    title="Eliminar usuario">
                                                <i class="bi bi-trash"></i>
                                                <span class="d-none d-lg-inline"> Eliminar</span>
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-person-check"></i> Usuario actual
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No hay usuarios registrados</h5>
                        <p class="text-muted">Comience agregando su primer usuario usando el formulario de arriba.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Función para confirmar eliminación
    function confirmarEliminacion(id, usuario) {
        if (confirm(`¿Está seguro de que desea eliminar el usuario "${usuario}"?\n\nEsta acción no se puede deshacer.`)) {
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