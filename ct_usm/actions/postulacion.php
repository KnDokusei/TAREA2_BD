<?php
// actions/postulacion.php — CRUD de postulaciones
session_start();
require_once '../config/db.php';
requireLogin();

$pdo    = getDB();
$accion = $_POST['accion'] ?? '';
$id_rol = (int)$_SESSION['id_rol'];
$id_usr = (int)$_SESSION['id_usuario'];

// ── Número y código únicos ─────────────────────────────────
function generarNumeroPostulacion(PDO $pdo): string {
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM POSTULACION WHERE numero_postulacion LIKE 'POST-{$year}-%'");
    $n    = (int)$stmt->fetchColumn() + 1;
    return sprintf('POST-%d-%03d', $year, $n);
}
function generarCodigoInterno(PDO $pdo): string {
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM POSTULACION WHERE codigo_interno LIKE 'CI-{$year}-%'");
    $n    = (int)$stmt->fetchColumn() + 1;
    return sprintf('CI-%d-%03d', $year, $n);
}

// ── CREAR ──────────────────────────────────────────────────
if ($accion === 'crear' && $id_rol === 1) {

    // Validar campos obligatorios
    $required = ['id_tipo_iniciativa','id_sede','id_region_ejecucion','id_region_impacto',
                 'fecha_inicio','fecha_termino','presupuesto_total','objetivo'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            setFlash('danger', "El campo «{$field}» es obligatorio.");
            redirect('../app.php?page=nueva_postulacion');
        }
    }

    // Empresa: existente o nueva
    $id_empresa = null;
    if (!empty($_POST['id_empresa_existente'])) {
        $id_empresa = (int)$_POST['id_empresa_existente'];
    } else {
        // Crear empresa nueva
        $rut   = trim($_POST['rut_empresa']    ?? '');
        $nomb  = trim($_POST['nombre_empresa'] ?? '');
        $tam   = (int)($_POST['id_tamano']     ?? 0);
        $conv  = (int)($_POST['convenio_marco']?? 0);
        $email = trim($_POST['email_empresa']  ?? '');

        if (!$rut || !$nomb || !$tam) {
            setFlash('danger', 'Completa los datos de la empresa externa.');
            redirect('../app.php?page=nueva_postulacion');
        }

        // Verificar si la empresa ya existe por RUT
        $chk = $pdo->prepare('SELECT id_empresa FROM EMPRESA WHERE rut_empresa = ?');
        $chk->execute([$rut]);
        $emp = $chk->fetch();

        if ($emp) {
            $id_empresa = (int)$emp['id_empresa'];
        } else {
            $insEmp = $pdo->prepare(
                'INSERT INTO EMPRESA (rut_empresa, nombre_empresa, id_tamano, convenio_marco, email_empresa)
                 VALUES (?,?,?,?,?)'
            );
            $insEmp->execute([$rut, $nomb, $tam, $conv, $email ?: null]);
            $id_empresa = (int)$pdo->lastInsertId();
        }
    }

    // Obtener id_estado Borrador
    $id_borrador = $pdo->query("SELECT id_estado FROM ESTADO_POSTULACION WHERE descripcion='Borrador' LIMIT 1")->fetchColumn();

    $numero = generarNumeroPostulacion($pdo);
    $codigo = generarCodigoInterno($pdo);

    $ins = $pdo->prepare(
        'INSERT INTO POSTULACION
         (numero_postulacion, codigo_interno, fecha_postulacion, objetivo,
          descripcion_soluciones, resultados_esperados, fecha_inicio, fecha_termino,
          presupuesto_total, id_tipo_iniciativa, id_estado, id_sede,
          id_region_ejecucion, id_region_impacto, id_empresa, id_usuario_creador)
         VALUES (?,?,CURDATE(),?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    $ins->execute([
        $numero, $codigo,
        trim($_POST['objetivo']),
        trim($_POST['descripcion_soluciones'] ?? '') ?: null,
        trim($_POST['resultados_esperados']   ?? '') ?: null,
        $_POST['fecha_inicio'],
        $_POST['fecha_termino'],
        (float)$_POST['presupuesto_total'],
        (int)$_POST['id_tipo_iniciativa'],
        $id_borrador,
        (int)$_POST['id_sede'],
        (int)$_POST['id_region_ejecucion'],
        (int)$_POST['id_region_impacto'],
        $id_empresa,
        $id_usr,
    ]);
    $id_nueva = (int)$pdo->lastInsertId();

    // Insertar equipo si venía en POST
    $personas_ids = $_POST['equipo_personas'] ?? [];
    $roles_eq     = $_POST['equipo_roles']    ?? [];
    foreach ($personas_ids as $k => $pid) {
        $pid = (int)$pid;
        $rol = trim($roles_eq[$k] ?? 'Colaborador');
        if ($pid > 0) {
            $insEq = $pdo->prepare('INSERT IGNORE INTO INTEGRANTE_EQUIPO (id_postulacion, id_persona, rol) VALUES (?,?,?)');
            $insEq->execute([$id_nueva, $pid, $rol]);
        }
    }

    // Insertar cronograma si venía en POST
    $etapas_nom  = $_POST['etapa_nombre']   ?? [];
    $etapas_desc = $_POST['etapa_desc']     ?? [];
    $etapas_sem  = $_POST['etapa_semanas']  ?? [];
    foreach ($etapas_nom as $k => $nombre) {
        $nombre = trim($nombre);
        if ($nombre) {
            $insEt = $pdo->prepare(
                'INSERT INTO ETAPA_CRONOGRAMA (id_postulacion, nombre_etapa, descripcion, semanas, orden)
                 VALUES (?,?,?,?,?)'
            );
            $insEt->execute([$id_nueva, $nombre, trim($etapas_desc[$k] ?? '') ?: null, (int)($etapas_sem[$k] ?? 4), $k+1]);
        }
    }

    setFlash('success', "Postulación {$numero} creada exitosamente. Estado: Borrador.");
    redirect('../app.php?page=editar_postulacion&id=' . $id_nueva);
}

// ── EDITAR ─────────────────────────────────────────────────
if ($accion === 'editar' && $id_rol === 1) {
    $id_post = (int)($_POST['id_postulacion'] ?? 0);

    // Verificar que sea del usuario y esté en Borrador
    $chk = $pdo->prepare("SELECT id_postulacion FROM POSTULACION p
        JOIN ESTADO_POSTULACION ep ON p.id_estado=ep.id_estado
        WHERE p.id_postulacion=? AND p.id_usuario_creador=? AND ep.descripcion='Borrador'");
    $chk->execute([$id_post, $id_usr]);
    if (!$chk->fetch()) {
        setFlash('danger', 'No puedes editar esta postulación.');
        redirect('../app.php?page=mis_postulaciones');
    }

    // Empresa: si viene id_empresa directamente (edición usa select de existentes)
    $id_empresa = (int)($_POST['id_empresa'] ?? 0);
    if (!$id_empresa) {
        setFlash('danger', 'Debes seleccionar una empresa.');
        redirect('../app.php?page=editar_postulacion&id='.$id_post);
    }

    $upd = $pdo->prepare(
        'UPDATE POSTULACION SET
            objetivo=?, descripcion_soluciones=?, resultados_esperados=?,
            fecha_inicio=?, fecha_termino=?, presupuesto_total=?,
            id_tipo_iniciativa=?, id_sede=?, id_region_ejecucion=?,
            id_region_impacto=?, id_empresa=?
         WHERE id_postulacion=?'
    );
    $upd->execute([
        trim($_POST['objetivo']),
        trim($_POST['descripcion_soluciones'] ?? '') ?: null,
        trim($_POST['resultados_esperados']   ?? '') ?: null,
        $_POST['fecha_inicio'],
        $_POST['fecha_termino'],
        (float)$_POST['presupuesto_total'],
        (int)$_POST['id_tipo_iniciativa'],
        (int)$_POST['id_sede'],
        (int)$_POST['id_region_ejecucion'],
        (int)$_POST['id_region_impacto'],
        $id_empresa,
        $id_post,
    ]);

    setFlash('success', 'Postulación actualizada correctamente.');
    redirect('../app.php?page=editar_postulacion&id='.$id_post);
}

// ── ENVIAR (usa Stored Procedure) ─────────────────────────
if ($accion === 'enviar' && $id_rol === 1) {
    $id_post = (int)($_POST['id_postulacion'] ?? 0);

    // Verificar propiedad
    $chk = $pdo->prepare('SELECT id_postulacion FROM POSTULACION WHERE id_postulacion=? AND id_usuario_creador=?');
    $chk->execute([$id_post, $id_usr]);
    if (!$chk->fetch()) {
        setFlash('danger', 'No tienes permisos sobre esta postulación.');
        redirect('../app.php?page=mis_postulaciones');
    }

    // Llamar al Stored Procedure
    $stmt = $pdo->prepare('CALL sp_enviar_postulacion(?, ?, @resultado)');
    $stmt->execute([$id_post, $id_usr]);
    $stmt->closeCursor();

    $res = $pdo->query('SELECT @resultado AS resultado')->fetchColumn();

    if (str_starts_with($res, 'OK')) {
        setFlash('success', $res);
        redirect('../app.php?page=ver_postulacion&id='.$id_post);
    } else {
        setFlash('danger', $res);
        redirect('../app.php?page=ver_postulacion&id='.$id_post);
    }
}

// ── ELIMINAR ───────────────────────────────────────────────
if ($accion === 'eliminar' && $id_rol === 1) {
    $id_post = (int)($_POST['id_postulacion'] ?? 0);

    $chk = $pdo->prepare("SELECT id_postulacion FROM POSTULACION p
        JOIN ESTADO_POSTULACION ep ON p.id_estado=ep.id_estado
        WHERE p.id_postulacion=? AND p.id_usuario_creador=? AND ep.descripcion='Borrador'");
    $chk->execute([$id_post, $id_usr]);
    if (!$chk->fetch()) {
        setFlash('danger', 'Solo puedes eliminar tus borradores propios.');
        redirect('../app.php?page=mis_postulaciones');
    }

    // Eliminar dependientes
    $pdo->prepare('DELETE FROM INTEGRANTE_EQUIPO WHERE id_postulacion=?')->execute([$id_post]);
    $pdo->prepare('DELETE FROM ETAPA_CRONOGRAMA  WHERE id_postulacion=?')->execute([$id_post]);
    $pdo->prepare('DELETE FROM DOCUMENTO         WHERE id_postulacion=?')->execute([$id_post]);
    $pdo->prepare('DELETE FROM LOG_ESTADO_POSTULACION WHERE id_postulacion=?')->execute([$id_post]);
    $pdo->prepare('DELETE FROM POSTULACION        WHERE id_postulacion=?')->execute([$id_post]);

    setFlash('success', 'Borrador eliminado correctamente.');
    redirect('../app.php?page=mis_postulaciones');
}

setFlash('danger', 'Acción no reconocida.');
redirect('../app.php?page=inicio');
