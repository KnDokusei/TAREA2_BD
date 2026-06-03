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

// ---- VERIFICACIÓN DE PERMISOS -------------------------------------------
// El evaluador (ROL 2) solo puede registrar/editar evaluaciones de postulaciones
// que le están asignadas (hay una fila en EVALUACION con su id_usuario)
// O bien que aún no tienen evaluador asignado (primera vez que evalúa).
// Si la postulación ya tiene evaluación de OTRO evaluador, se bloquea el acceso.
$evalExistente = $pdo->prepare(
    'SELECT id_usuario FROM EVALUACION WHERE id_postulacion = ? LIMIT 1'
);
$evalExistente->execute([$id_post]);
$propietario = $evalExistente->fetchColumn();

if ($propietario !== false && (int)$propietario !== $id_usr) {
    setFlash('danger', 'No tienes permiso para modificar la evaluación de esta postulación. Fue asignada a otro evaluador.');
    redirect('../app.php?page=ver_postulacion&id=' . $id_post);
}
// ---- FIN VERIFICACIÓN -----------------------------------------------------

// Verificar que la postulación exista y esté en estado evaluable
$estadoPost = $pdo->prepare(
    "SELECT ep.descripcion FROM POSTULACION p
     JOIN ESTADO_POSTULACION ep ON p.id_estado = ep.id_estado
     WHERE p.id_postulacion = ? LIMIT 1"
);
$estadoPost->execute([$id_post]);
$estadoActual = $estadoPost->fetchColumn();

if (!$estadoActual) {
    setFlash('danger', 'Postulación no encontrada.');
    redirect('../app.php?page=evaluaciones');
}

// -- UPSERT evaluación -------------------------------------------------------
// ON DUPLICATE KEY UPDATE garantiza que si ya existe un registro para
// (id_postulacion, id_usuario) se actualiza en lugar de insertar uno nuevo.
// Esto depende del constraint UNIQUE uq_eval_post_usuario en la tabla EVALUACION.
// Si por alguna razón el constraint no existe, se hace un UPDATE explícito.
$chkEval = $pdo->prepare(
    'SELECT id_evaluacion FROM EVALUACION WHERE id_postulacion = ? AND id_usuario = ?'
);
$chkEval->execute([$id_post, $id_usr]);
$idEval = $chkEval->fetchColumn();

if ($idEval) {
    // Ya existe: UPDATE directo (evita cualquier riesgo de duplicado)
    $upd = $pdo->prepare(
        'UPDATE EVALUACION SET puntaje=?, comentario=?, fecha_evaluacion=NOW()
         WHERE id_evaluacion=?'
    );
    $upd->execute([$puntaje, $coment ?: null, $idEval]);
} else {
    // No existe: INSERT nuevo
    $ins = $pdo->prepare(
        'INSERT INTO EVALUACION (id_postulacion, id_usuario, puntaje, comentario)
         VALUES (?,?,?,?)'
    );
    $ins->execute([$id_post, $id_usr, $puntaje, $coment ?: null]);
}

// -- Actualizar estado de la postulación ------------------------------------
// El TRIGGER trg_log_cambio_estado registrará el cambio en LOG_ESTADO_POSTULACION
$id_estado_stmt = $pdo->prepare(
    'SELECT id_estado FROM ESTADO_POSTULACION WHERE descripcion=? LIMIT 1'
);
$id_estado_stmt->execute([$nuevo_estado]);
$id_est = $id_estado_stmt->fetchColumn();

if ($id_est) {
    $updPost = $pdo->prepare('UPDATE POSTULACION SET id_estado=? WHERE id_postulacion=?');
    $updPost->execute([$id_est, $id_post]);
}

setFlash('success', 'Evaluación registrada correctamente. Estado actualizado a: ' . $nuevo_estado);
redirect('../app.php?page=ver_postulacion&id=' . $id_post);
