<?php
// actions/cronograma.php — CRUD etapas cronograma
session_start();
require_once '../config/db.php';
requireLogin();
requireRol([1]);

$pdo     = getDB();
$accion  = $_POST['accion']              ?? '';
$id_post = (int)($_POST['id_postulacion'] ?? 0);
$id_usr  = (int)$_SESSION['id_usuario'];

// Verificar propiedad y estado Borrador
$chk = $pdo->prepare(
    "SELECT id_postulacion FROM POSTULACION p
     JOIN ESTADO_POSTULACION ep ON p.id_estado=ep.id_estado
     WHERE p.id_postulacion=? AND p.id_usuario_creador=? AND ep.descripcion='Borrador'"
);
$chk->execute([$id_post, $id_usr]);
if (!$chk->fetch()) {
    setFlash('danger', 'No puedes modificar el cronograma de esta postulación.');
    redirect('../app.php?page=mis_postulaciones');
}

if ($accion === 'agregar') {
    $nombre  = trim($_POST['nombre_etapa'] ?? '');
    $desc    = trim($_POST['descripcion']  ?? '') ?: null;
    $semanas = max(1, (int)($_POST['semanas'] ?? 4));

    if (!$nombre) {
        setFlash('danger', 'El nombre de la etapa es obligatorio.');
        redirect('../app.php?page=editar_postulacion&id='.$id_post);
    }

    // Calcular nuevo orden
    $maxOrd = $pdo->prepare('SELECT COALESCE(MAX(orden),0)+1 FROM ETAPA_CRONOGRAMA WHERE id_postulacion=?');
    $maxOrd->execute([$id_post]);
    $orden = (int)$maxOrd->fetchColumn();

    $ins = $pdo->prepare(
        'INSERT INTO ETAPA_CRONOGRAMA (id_postulacion, nombre_etapa, descripcion, semanas, orden)
         VALUES (?,?,?,?,?)'
    );
    $ins->execute([$id_post, $nombre, $desc, $semanas, $orden]);
    setFlash('success', 'Etapa agregada al cronograma.');
}

if ($accion === 'eliminar') {
    $id_etapa = (int)($_POST['id_etapa'] ?? 0);
    $del = $pdo->prepare('DELETE FROM ETAPA_CRONOGRAMA WHERE id_etapa=? AND id_postulacion=?');
    $del->execute([$id_etapa, $id_post]);
    setFlash('success', 'Etapa eliminada.');
}

redirect('../app.php?page=editar_postulacion&id='.$id_post);
