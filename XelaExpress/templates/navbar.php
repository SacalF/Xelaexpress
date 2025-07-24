<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/dashboard.php">XelaExpress</a>
        <div class="d-flex">
            <?php if (isset($_SESSION['usuario'])): ?>
                <span class="me-3">Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <a href="/logout.php" class="btn btn-outline-danger">Salir</a>
            <?php else: ?>
                <a href="/login.php" class="btn btn-primary">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </div>
</nav> 