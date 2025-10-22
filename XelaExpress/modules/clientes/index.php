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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - XelaExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../../templates/sidebar.php'; ?>
    
    <div class="container-fluid">
        <div class="d-flex flex-column mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h2 class="h3 mb-0">Gestión de Clientes</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="exportarClientes()">
                        <i class="bi bi-download me-1"></i>Exportar
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="mostrarFormulario()">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo Cliente
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
        <div class="card-header"><?php echo $cliente_editar ? 'Editar cliente' : 'Registrar nuevo cliente'; ?></div>
        <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <?php if ($cliente_editar): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($cliente_editar['id']) ?>">
                    <?php endif; ?>
                    
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="nombre" 
                                   class="form-control" 
                                   placeholder="Ej: Juan" 
                                   required 
                                   minlength="2"
                                   maxlength="50"
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                                   title="Solo letras y espacios"
                                   value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['nombre']) : '' ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="apellido" 
                                   class="form-control" 
                                   placeholder="Ej: Pérez" 
                                   required 
                                   minlength="2"
                                   maxlength="50"
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                                   title="Solo letras y espacios"
                                   value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['apellido']) : '' ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" 
                                   name="telefono" 
                                   class="form-control" 
                                   placeholder="Ej: 5025-1234"
                                   pattern="[0-9+\-\s()]+"
                                   maxlength="20"
                                   title="Formato: números, +, -, espacios y paréntesis"
                                   value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['telefono']) : '' ?>">
                            <div class="form-text text-muted">
                                <small>Ej: 5025-1234 o 5025 1234</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" 
                                   name="correo" 
                                   class="form-control" 
                                   placeholder="Ej: juan.perez@email.com"
                                   maxlength="100"
                                   value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['correo']) : '' ?>">
                            <div class="form-text text-muted">
                                <small>Ej: usuario@dominio.com</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Dirección</label>
                            <input type="text" 
                                   name="direccion" 
                                   class="form-control" 
                                   placeholder="Ej: Zona 10, Ciudad"
                                   maxlength="200"
                                   value="<?= $cliente_editar ? htmlspecialchars($cliente_editar['direccion']) : '' ?>">
                            <div class="form-text text-muted">
                                <small>Dirección completa (opcional)</small>
                            </div>
                        </div>
                        
                        <div class="col-md-1 d-flex flex-column gap-2">
                            <?php if ($cliente_editar): ?>
                                <button type="submit" name="actualizar" class="btn btn-success">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            <?php else: ?>
                                <button type="submit" name="registrar" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
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
            const primerInput = formulario.querySelector('input[name="nombre"]');
            if (primerInput) {
                primerInput.focus();
            }
        }
    }

    // Función para exportar clientes
    function exportarClientes() {
        const tabla = document.querySelector('.table');
        if (tabla) {
            const csv = tablaToCSV(tabla);
            downloadCSV(csv, 'clientes.csv');
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

    // Validación en tiempo real para campos de texto
    function validateTextField(input, pattern, minLength = 2, maxLength = 50) {
        const value = input.value.trim();
        
        if (value.length === 0) {
            input.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        if (value.length >= minLength && value.length <= maxLength && pattern.test(value)) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        }
    }

    // Validación para nombres y apellidos
    document.querySelectorAll('input[name="nombre"], input[name="apellido"]').forEach(input => {
        input.addEventListener('input', function() {
            const pattern = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
            validateTextField(this, pattern, 2, 50);
        });
    });

    // Validación para teléfono
    document.querySelector('input[name="telefono"]').addEventListener('input', function() {
        const value = this.value.trim();
        const pattern = /^[0-9+\-\s()]+$/;
        
        if (value.length === 0) {
            this.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        if (pattern.test(value) && value.length >= 8 && value.length <= 20) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });

    // Validación para email
    document.querySelector('input[name="correo"]').addEventListener('input', function() {
        const value = this.value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value.length === 0) {
            this.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        if (emailPattern.test(value) && value.length <= 100) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
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
</body>
</html> 