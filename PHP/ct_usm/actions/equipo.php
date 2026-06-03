<?php
// actions/equipo.php — CRUD integrantes de equipo
session_start();
require_once '../config/db.php';
requireLogin();
requireRol([1]);

$pdo     = getDB();
$accion  = $_POST['accion']        ?? '';
$id_post = (int)($_POST['id_postulacion'] ?? 0);
$id_usr  = (int)$_SESSION['id_usuario'];

// Verificar que la postulación sea del usuario y esté en Borrador
$chk = $pdo->prepare(
    "SELECT id_postulacion FROM POSTULACION p
     JOIN ESTADO_POSTULACION ep ON p.id_estado=ep.id_estado
     WHERE p.id_postulacion=? AND p.id_usuario_creador=? AND ep.descripcion='Borrador'"
);
$chk->execute([$id_post, $id_usr]);
if (!$chk->fetch()) {
    setFlash('danger', 'No puedes modificar el equipo de esta postulación.');
    redirect('../app.php?page=mis_postulaciones');
}

if ($accion === 'agregar') {
    $id_persona = (int)($_POST['id_persona'] ?? 0);
    $rol        = trim($_POST['rol'] ?? 'Colaborador');

    if (!$id_persona) {
        setFlash('danger', 'Selecciona una persona.');
        redirect('../app.php?page=editar_postulacion&id='.$id_post);
    }

    $ins = $pdo->prepare(
        'INSERT IGNORE INTO INTEGRANTE_EQUIPO (id_postulacion, id_persona, rol) VALUES (?,?,?)'
    );
    $ins->execute([$id_post, $id_persona, $rol]);
    setFlash('success', 'Integrante agregado al equipo.');
}

if ($accion === 'quitar') {
    $id_persona = (int)($_POST['id_persona'] ?? 0);
    $del = $pdo->prepare('DELETE FROM INTEGRANTE_EQUIPO WHERE id_postulacion=? AND id_persona=?');
    $del->execute([$id_post, $id_persona]);
    setFlash('success', 'Integrante eliminado del equipo.');
}

redirect('../app.php?page=editar_postulacion&id='.$id_post);
