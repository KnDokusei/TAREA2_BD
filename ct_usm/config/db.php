<?php
// ============================================================
// config/db.php — Conexión PDO a MySQL
// CT-USM Postulaciones
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ct_usm_postulaciones');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,   // Prepared statements reales (anti-SQLi)
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;padding:20px;color:red">
                 <strong>Error de conexión a la base de datos:</strong><br>'
                 . htmlspecialchars($e->getMessage()) .
                 '<br><br>Verifique que MySQL esté activo en XAMPP y que la base de datos
                 <strong>ct_usm_postulaciones</strong> exista.</div>');
        }
    }
    return $pdo;
}

// ── Helper: mensaje flash ──────────────────────────────────
function setFlash(string $tipo, string $mensaje): void {
    $_SESSION['flash_tipo']    = $tipo;
    $_SESSION['flash_mensaje'] = $mensaje;
}

function getFlash(): string {
    if (!isset($_SESSION['flash_mensaje'])) return '';
    $tipo = $_SESSION['flash_tipo'] ?? 'info';
    $msg  = htmlspecialchars($_SESSION['flash_mensaje']);
    unset($_SESSION['flash_tipo'], $_SESSION['flash_mensaje']);

    $clase = match($tipo) {
        'success' => 'alert-success',
        'danger'  => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };
    return "<div class=\"alert {$clase} alert-dismissible fade show\" role=\"alert\">
                {$msg}
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
            </div>";
}

// ── Helper: redirección segura ─────────────────────────────
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ── Helper: verificar sesión activa ───────────────────────
function requireLogin(): void {
    if (empty($_SESSION['id_usuario'])) {
        redirect('../index.php');
    }
}

// ── Helper: verificar rol ─────────────────────────────────
function requireRol(array $roles): void {
    requireLogin();
    if (!in_array($_SESSION['id_rol'], $roles, true)) {
        setFlash('danger', 'No tienes permisos para acceder a esta sección.');
        redirect('../app.php?page=inicio');
    }
}
