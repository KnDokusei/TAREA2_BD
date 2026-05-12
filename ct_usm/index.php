<?php
// ============================================================
// index.php — Página de Login
// CT-USM Postulaciones
// ============================================================
session_start();

// Si ya hay sesión activa, ir directo al app
if (!empty($_SESSION['id_usuario'])) {
    header('Location: app.php?page=inicio');
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CT-USM — Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center justify-content-center min-vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <!-- Logo / Encabezado -->
            <div class="text-center mb-4">
                <div class="login-logo-icon mx-auto mb-3">
                    <span class="fw-bold text-warning fs-5">USM</span>
                </div>
                <h1 class="h4 fw-bold text-white mb-1">CT-USM</h1>
                <p class="text-white-50 small">Sistema de Postulación de Iniciativas</p>
            </div>

            <!-- Card Login -->
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-1">Iniciar sesión</h2>
                    <p class="text-muted small mb-4">Accede con tus credenciales institucionales</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="actions/auth.php" novalidate>
                        <input type="hidden" name="accion" value="login">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="nombre_usuario" class="form-control"
                                       placeholder="nombre.apellido" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control"
                                       placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-usm w-100 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar al sistema
                        </button>
                    </form>

                    <!-- Accesos rápidos de prueba -->
                    <hr class="my-3">
                    <p class="text-muted small mb-2">Usuarios de prueba <span class="badge bg-light text-dark">contraseña: <code>password</code></span></p>
                    <div class="d-flex flex-wrap gap-1">
                        <button class="btn btn-outline-secondary btn-sm" onclick="fillLogin('carlos.ramirez')">
                            Postulante
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="fillLogin('rodrigo.vega')">
                            Evaluador
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="fillLogin('admin')">
                            Admin
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function fillLogin(usuario) {
    document.querySelector('[name=nombre_usuario]').value = usuario;
    document.querySelector('[name=password]').value = 'password';
}
</script>
</body>
</html>
