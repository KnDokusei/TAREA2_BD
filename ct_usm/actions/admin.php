<?php
// actions/admin.php — Acciones administrativas ROL 3
session_start();
require_once '../config/db.php';
requireLogin();
requireRol([3]);

$pdo    = getDB();
$accion = $_POST['accion'] ?? '';

if ($accion === 'toggle_usuario') {
    $id  = (int)($_POST['id_usuario'] ?? 0);
    $act = (int)($_POST['activo']     ?? 0);
    $pdo->prepare('UPDATE USUARIO SET activo=? WHERE id_usuario=?')->execute([$act, $id]);
    setFlash('success', 'Estado del usuario actualizado.');
}

if ($accion === 'asignar_evaluador') {
    // Insertar evaluación vacía para indicar asignación
    $id_post = (int)($_POST['id_postulacion'] ?? 0);
    $id_eval = (int)($_POST['id_evaluador']   ?? 0);

    if ($id_post && $id_eval) {
        // Cambiar estado a En Revisión si estaba Enviada
        $id_en_rev = $pdo->query("SELECT id_estado FROM ESTADO_POSTULACION WHERE descripcion='En Revisión' LIMIT 1")->fetchColumn();
        $id_enviada = $pdo->query("SELECT id_estado FROM ESTADO_POSTULACION WHERE descripcion='Enviada' LIMIT 1")->fetchColumn();
        $pdo->prepare(
            "UPDATE POSTULACION SET id_estado=? WHERE id_postulacion=? AND id_estado=?"
        )->execute([$id_en_rev, $id_post, $id_enviada]);

        // Crear evaluación placeholder para registrar asignación
        $pdo->prepare(
            'INSERT IGNORE INTO EVALUACION (id_postulacion, id_usuario, comentario)
             VALUES (?,?,?)'
        )->execute([$id_post, $id_eval, 'Asignado por administrador']);

        setFlash('success', 'Evaluador asignado y postulación puesta En Revisión.');
    }
}

if ($accion === 'crear_evaluador') {
    $nombre   = trim($_POST['nombre']         ?? '');
    $apellido = trim($_POST['apellido']        ?? '');
    $email    = trim($_POST['email']           ?? '');
    $usuario  = trim($_POST['nombre_usuario']  ?? '');
    $password = $_POST['password']             ?? '';

    if (!$nombre || !$email || !$usuario || !$password) {
        setFlash('danger', 'Todos los campos son obligatorios.');
        redirect('../app.php?page=admin');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $id_rol_eval = $pdo->query("SELECT id_rol FROM ROL WHERE nombre_rol='Coordinador' LIMIT 1")->fetchColumn();

    try {
        $pdo->prepare(
            'INSERT INTO USUARIO (nombre_usuario, password_hash, nombre, apellido, email, id_rol)
             VALUES (?,?,?,?,?,?)'
        )->execute([$usuario, $hash, $nombre, $apellido, $email, $id_rol_eval]);
        setFlash('success', "Evaluador {$nombre} {$apellido} creado correctamente.");
    } catch (PDOException $e) {
        setFlash('danger', 'Error: el usuario o email ya existe.');
    }
}

redirect('../app.php?page=admin');
