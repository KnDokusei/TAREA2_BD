<?php
// actions/evaluacion.php — Registrar/editar evaluación (ROL 2)
session_start();
require_once '../config/db.php';
requireLogin();
requireRol([2]);

$pdo     = getDB();
$id_usr  = (int)$_SESSION['id_usuario'];
$id_post = (int)($_POST['id_postulacion'] ?? 0);
$puntaje = (float)($_POST['puntaje']      ?? 0);
$coment  = trim($_POST['comentario']      ?? '');
$nuevo_estado = trim($_POST['nuevo_estado'] ?? '');

if (!$id_post || !$nuevo_estado) {
    setFlash('danger', 'Datos incompletos para registrar la evaluación.');
    redirect('../app.php?page=evaluaciones');
}

// Upsert evaluación (INSERT o UPDATE por constraint uq_eval_post_usuario)
$stmt = $pdo->prepare(
    'INSERT INTO EVALUACION (id_postulacion, id_usuario, puntaje, comentario)
     VALUES (?,?,?,?)
     ON DUPLICATE KEY UPDATE puntaje=VALUES(puntaje), comentario=VALUES(comentario), fecha_evaluacion=NOW()'
);
$stmt->execute([$id_post, $id_usr, $puntaje, $coment ?: null]);

// Actualizar estado de la postulación (el TRIGGER registrará el cambio en LOG)
$id_estado = $pdo->prepare('SELECT id_estado FROM ESTADO_POSTULACION WHERE descripcion=? LIMIT 1');
$id_estado->execute([$nuevo_estado]);
$id_est = $id_estado->fetchColumn();

if ($id_est) {
    $upd = $pdo->prepare('UPDATE POSTULACION SET id_estado=? WHERE id_postulacion=?');
    $upd->execute([$id_est, $id_post]);
}

setFlash('success', 'Evaluación registrada correctamente. Estado actualizado a: '.$nuevo_estado);
redirect('../app.php?page=ver_postulacion&id='.$id_post);
