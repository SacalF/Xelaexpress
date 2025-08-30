        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i>Lista de productos</span>
                <span class="badge bg-primary"><?= count($productos) ?> productos</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 200px;">Producto</th>
                                <th class="text-end" style="min-width: 100px;">Precio</th>
                                <th class="text-center" style="min-width: 80px;">Stock</th>
                                <th class="text-end" style="min-width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($productos)): ?>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></div>
                                        <?php if (!empty($p['descripcion'])): ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($p['descripcion']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">Q <?= number_format($p['precio'], 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($p['stock'] <= 5): ?>
                                            <span class="badge bg-danger"><?= $p['stock'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?= $p['stock'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="?editar=<?= $p['id'] ?>" class="btn btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="confirmarEliminacion(<?= $p['id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No hay productos registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
