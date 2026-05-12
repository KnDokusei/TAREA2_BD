<?php
// pages/evaluaciones.php — ROL 2 y 3
requireRol([2, 3]);
include_once 'includes/badge_estado.php';

$pdo    = getDB();
$id_rol = (int)$_SESSION['id_rol'];
$id_usr = (int)$_SESSION['id_usuario'];

// ------------------------------------------------------------------
// Postulaciones pendientes de evaluar
// ROL 2: solo las que le pertenecen (ya tiene fila en EVALUACION con
//        su id_usuario) o que aún no tienen evaluador asignado.
// ROL 3: todas las pendientes.
// ------------------------------------------------------------------
if ($id_rol === 2) {
    $stmtPend = $pdo->prepare(
        "SELECT v.* FROM VW_POSTULACIONES_COMPLETAS v
         WHERE v.estado IN ('Enviada','En Revisión')
           AND (
               -- Sin evaluador asignado aún
               NOT EXISTS (SELECT 1 FROM EVALUACION e WHERE e.id_postulacion = v.id_postulacion)
               OR
               -- O asignada a este evaluador
               EXISTS (SELECT 1 FROM EVALUACION e
                       WHERE e.id_postulacion = v.id_postulacion
                         AND e.id_usuario = ?)
           )
         ORDER BY v.fecha_postulacion ASC"
    );
    $stmtPend->execute([$id_usr]);
} else {
    $stmtPend = $pdo->query(
        "SELECT v.* FROM VW_POSTULACIONES_COMPLETAS v
         WHERE v.estado IN ('Enviada','En Revisión')
         ORDER BY v.fecha_postulacion ASC"
    );
}
$pendientes = $stmtPend->fetchAll();

// ------------------------------------------------------------------
// Evaluaciones registradas
// Se usa una subquery con MAX(id_evaluacion) por postulación+usuario
// para garantizar UNA sola fila por combinación, eliminando cualquier
// duplicado residual que pueda existir en la tabla EVALUACION.
// ROL 2: solo las propias. ROL 3: todas.
// ------------------------------------------------------------------
if ($id_rol === 2) {
    $stmtEval = $pdo->prepare(
        "SELECT ev.id_evaluacion, ev.id_postulacion, ev.id_usuario,
                ev.puntaje, ev.comentario, ev.fecha_evaluacion,
                v.numero_postulacion, v.objetivo, v.nombre_empresa, v.estado,
                CONCAT(u.nombre,' ',u.apellido) AS evaluador
         FROM EVALUACION ev
         JOIN (
             -- Tomar solo el id_evaluacion más reciente por (postulación, usuario)
             -- Esto neutraliza cualquier duplicado residual en BD
             SELECT MAX(id_evaluacion) AS max_id
             FROM EVALUACION
             WHERE id_usuario = ?
             GROUP BY id_postulacion
         ) dedup ON ev.id_evaluacion = dedup.max_id
         JOIN VW_POSTULACIONES_COMPLETAS v ON ev.id_postulacion = v.id_postulacion
         JOIN USUARIO u ON ev.id_usuario = u.id_usuario
         ORDER BY ev.fecha_evaluacion DESC"
    );
    $stmtEval->execute([$id_usr]);
} else {
    // ROL 3 (admin): muestra todas, también deduplicadas por (postulacion, usuario)
    $stmtEval = $pdo->query(
        "SELECT ev.id_evaluacion, ev.id_postulacion, ev.id_usuario,
                ev.puntaje, ev.comentario, ev.fecha_evaluacion,
                v.numero_postulacion, v.objetivo, v.nombre_empresa, v.estado,
                CONCAT(u.nombre,' ',u.apellido) AS evaluador
         FROM EVALUACION ev
         JOIN (
             SELECT MAX(id_evaluacion) AS max_id
             FROM EVALUACION
             GROUP BY id_postulacion, id_usuario
         ) dedup ON ev.id_evaluacion = dedup.max_id
         JOIN VW_POSTULACIONES_COMPLETAS v ON ev.id_postulacion = v.id_postulacion
         JOIN USUARIO u ON ev.id_usuario = u.id_usuario
         ORDER BY ev.fecha_evaluacion DESC"
    );
}
$evaluadas = $stmtEval->fetchAll();
?>

<ul class="nav nav-tabs mb-3" id="evalTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-pendientes">
            Pendientes <span class="badge bg-warning text-dark ms-1"><?= count($pendientes) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-evaluadas">
            Evaluadas <span class="badge bg-secondary ms-1"><?= count($evaluadas) ?></span>
        </a>
    </li>
</ul>

<div class="tab-content">

    <!-- Pendientes -->
    <div class="tab-pane fade show active" id="tab-pendientes">
        <?php if (empty($pendientes)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                    No hay postulaciones pendientes de evaluación.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($pendientes as $p): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <p class="fw-semibold mb-1"><?= htmlspecialchars($p['objetivo']) ?></p>
                            <p class="text-muted small mb-0">
                                <?= htmlspecialchars($p['numero_postulacion']) ?>
                                &bull; <?= htmlspecialchars($p['nombre_empresa']) ?>
                                &bull; <?= htmlspecialchars($p['sede']) ?>
                            </p>
                        </div>
                        <div class="col-md-2 text-muted small">
                            <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($p['fecha_postulacion'])) ?>
                        </div>
                        <div class="col-md-2 fw-medium small">
                            $<?= number_format($p['presupuesto_total'], 0, ',', '.') ?>
                        </div>
                        <div class="col-md-1">
                            <?= badge_estado($p['estado']) ?>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="app.php?page=ver_postulacion&id=<?= $p['id_postulacion'] ?>"
                               class="btn btn-usm btn-sm">
                                <i class="bi bi-clipboard-check me-1"></i>Evaluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Evaluadas -->
    <div class="tab-pane fade" id="tab-evaluadas">
        <?php if (empty($evaluadas)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No hay evaluaciones registradas aún.
                </div>
            </div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Postulación</th>
                                <th>Empresa</th>
                                <th>Estado</th>
                                <th>Puntaje</th>
                                <th>Evaluador</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($evaluadas as $ev): ?>
                            <tr>
                                <td class="px-3">
                                    <span class="fw-semibold text-muted d-block"><?= htmlspecialchars($ev['numero_postulacion']) ?></span>
                                    <span><?= htmlspecialchars(mb_strimwidth($ev['objetivo'], 0, 45, '…')) ?></span>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($ev['nombre_empresa']) ?></td>
                                <td><?= badge_estado($ev['estado']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;width:60px">
                                            <div class="progress-bar bg-usm" style="width:<?= $ev['puntaje'] ?>%"></div>
                                        </div>
                                        <span class="fw-semibold"><?= number_format($ev['puntaje'], 1) ?></span>
                                    </div>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($ev['evaluador']) ?></td>
                                <td class="text-muted"><?= date('d/m/Y', strtotime($ev['fecha_evaluacion'])) ?></td>
                                <td>
                                    <a href="app.php?page=ver_postulacion&id=<?= $ev['id_postulacion'] ?>"
                                       class="btn btn-outline-secondary btn-sm py-0 px-2">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>
