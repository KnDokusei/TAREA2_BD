<?php
// ============================================================
// actions/auth.php — Login y Logout
// CT-USM Postulaciones
// ============================================================
session_start();
require_once '../config/db.php';

$accion = $_REQUEST['accion'] ?? '';

// ── LOGOUT ────────────────────────────────────────────────
if ($accion === 'logout') {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// ── LOGIN ─────────────────────────────────────────────────
if ($accion === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitizar entradas (anti SQL injection: se usan prepared statements)
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $password       = $_POST['password'] ?? '';

    if (empty($nombre_usuario) || empty($password)) {
        $_SESSION['login_error'] = 'Ingresa usuario y contraseña.';
        header('Location: ../index.php');
        exit;
    }

    $pdo  = getDB();
    // Prepared statement: protección contra inyección SQL
    $stmt = $pdo->prepare(
        'SELECT u.id_usuario, u.nombre_usuario, u.password_hash,
                u.nombre, u.apellido, u.email,
                u.id_rol, r.nombre_rol, u.activo
         FROM USUARIO u
         JOIN ROL r ON u.id_rol = r.id_rol
         WHERE u.nombre_usuario = ?
         LIMIT 1'
    );
    $stmt->execute([$nombre_usuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
        header('Location: ../index.php');
        exit;
    }

    if (!(bool)$usuario['activo']) {
        $_SESSION['login_error'] = 'Tu cuenta está desactivada. Contacta al administrador.';
        header('Location: ../index.php');
        exit;
    }

    if (!password_verify($password, $usuario['password_hash'])) {
        $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
        header('Location: ../index.php');
        exit;
    }

    // Login exitoso: guardar sesión
    $_SESSION['id_usuario']  = (int)$usuario['id_usuario'];
    $_SESSION['nombre']      = $usuario['nombre'];
    $_SESSION['apellido']    = $usuario['apellido'];
    $_SESSION['email']       = $usuario['email'];
    $_SESSION['id_rol']      = (int)$usuario['id_rol'];
    $_SESSION['rol_nombre']  = $usuario['nombre_rol'];

    header('Location: ../app.php?page=inicio');
    exit;
}

// Si llega aquí sin acción válida, redirigir a login
header('Location: ../index.php');
exit;
