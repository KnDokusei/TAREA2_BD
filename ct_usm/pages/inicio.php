<?php
// pages/inicio.php
include_once 'includes/badge_estado.php';
$pdo    = getDB();
$id_rol = (int)$_SESSION['id_rol'];
$id_usr = (int)$_SESSION['id_usuario'];

// ── Estadísticas según ROL ────────────────────────────────────────────────────
//  ROL 1 (Postulante)  → solo sus propias postulaciones
//  ROL 2 (Evaluador)   → solo las postulaciones que él evalúa
//  ROL 3 (Admin)       → todas
//
//  Presupuesto total: SOLO estados "activos" (Enviada, En Revisión, Aprobada)
//  NO incluye: Borrador, Rechazada, Cerrada, En Espera
// ─────────────────────────────────────────────────────────────────────────────

$estados_activos = "('Enviada','En Revisión','Aprobada')";

if ($id_rol === 1) {
    // Postulante: sus postulaciones
    $stmt_stats = $pdo->prepare(
        "SELECT
            COUNT(*)                                                             AS total,
            SUM(CASE WHEN ep.descripcion = 'Aprobada'    THEN 1 ELSE 0 END)    AS aprobadas,
            SUM(CASE WHEN ep.descripcion = 'En Revisión' THEN 1 ELSE 0 END)    AS en_revision,
            SUM(CASE WHEN ep.descripcion = 'Rechazada'   THEN 1 ELSE 0 END)    AS rechazadas,
            SUM(CASE WHEN ep.descripcion = 'Borrador'    THEN 1 ELSE 0 END)    AS borradores,
            COALESCE(SUM(CASE WHEN ep.descripcion IN ('Enviada','En Revisión','Aprobada')
                              THEN p.presupuesto_total ELSE 0 END), 0)          AS presupuesto_total
         FROM POSTULACION p
         JOIN ESTADO_POSTULACION ep ON p.id_estado = ep.id_estado
         WHERE p.id_usuario_creador = ?"
    );
    $stmt_stats->execute([$id_usr]);

} elseif ($id_rol === 2) {
    // Evaluador: postulaciones asignadas a él en EVALUACION
    $stmt_stats = $pdo->prepare(
        "SELECT
            COUNT(DISTINCT p.id_postulacion)                                     AS total,
            SUM(CASE WHEN ep.descripcion = 'Aprobada'    THEN 1 ELSE 0 END)    AS aprobadas,
            SUM(CASE WHEN ep.descripcion = 'En Revisión' THEN 1 ELSE 0 END)    AS en_revision,
            SUM(CASE WHEN ep.descripcion = 'Rechazada'   THEN 1 ELSE 0 END)    AS rechazadas,
            0                                                                    AS borradores,
            COALESCE(SUM(CASE WHEN ep.descripcion IN ('Enviada','En Revisión','Aprobada')
                              THEN p.presupuesto_total ELSE 0 END), 0)          AS presupuesto_total
         FROM POSTULACION p
         JOIN ESTADO_POSTULACION ep ON p.id_estado = ep.id_estado
         JOIN EVALUACION ev ON ev.id_postulacion = p.id_postulacion
         WHERE ev.id_usuario = ?"
    );
    $stmt_stats->execute([$id_usr]);

} else {
    // Admin: todo el sistema
    $stmt_stats = $pdo->prepare(
        "SELECT
            COUNT(*)                                                             AS total,
            SUM(CASE WHEN ep.descripcion = 'Aprobada'    THEN 1 ELSE 0 END)    AS aprobadas,
            SUM(CASE WHEN ep.descripcion = 'En Revisión' THEN 1 ELSE 0 END)    AS en_revision,
            SUM(CASE WHEN ep.descripcion = 'Rechazada'   THEN 1 ELSE 0 END)    AS rechazadas,
            SUM(CASE WHEN ep.descripcion = 'Borrador'    THEN 1 ELSE 0 END)    AS borradores,
            COALESCE(SUM(CASE WHEN ep.descripcion IN ('Enviada','En Revisión','Aprobada')
                              THEN p.presupuesto_total ELSE 0 END), 0)          AS presupuesto_total
         FROM POSTULACION p
         JOIN ESTADO_POSTULACION ep ON p.id_estado = ep.id_estado"
    );
    $stmt_stats->execute([]);
}

$stats = $stmt_stats->fetch();

// ── Postulaciones recientes (usa la VIEW) ────────────────────────────────────
if ($id_rol === 1) {
    $where_rec = 'WHERE v.id_usuario_creador = ' . $id_usr;
} elseif ($id_rol === 2) {
    $where_rec = "WHERE EXISTS(SELECT 1 FROM EVALUACION ev WHERE ev.id_postulacion = v.id_postulacion AND ev.id_usuario = {$id_usr}) AND v.estado != 'Borrador'";
} else {
    $where_rec = "WHERE v.estado != 'Borrador'";
}

$recientes = $pdo->query(
    "SELECT * FROM VW_POSTULACIONES_COMPLETAS v {$where_rec}
     ORDER BY v.fecha_postulacion DESC LIMIT 8"
)->fetchAll();

// Label del card de presupuesto según rol
$label_presupuesto = match($id_rol) {
    1 => 'Mis postulaciones activas',
    2 => 'Postulaciones que evalúo',
    default => 'Iniciativas activas (sistema)',
};
?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="stat-label">Total postulaciones</p>
                <p class="stat-value"><?= $stats['total'] ?></p>
                <p class="stat-sub text-muted small">Registradas en el sistema</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="stat-label">Aprobadas</p>
                <p class="stat-value text-success"><?= $stats['aprobadas'] ?></p>
                <p class="stat-sub text-muted small">Con financiamiento</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="stat-label">En revisión</p>
                <p class="stat-value text-primary"><?= $stats['en_revision'] ?></p>
                <p class="stat-sub text-muted small">Pendientes evaluación</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="stat-label">Presupuesto total</p>
                <p class="stat-value" style="font-size:1.4rem">$<?= number_format($stats['presupuesto_total'], 0, ',', '.') ?></p>
                <p class="stat-sub text-muted small"><?= htmlspecialchars($label_presupuesto) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla postulaciones recientes -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h2 class="h6 fw-semibold mb-0">
            <?= ($id_rol === 1) ? 'Mis postulaciones recientes' : 'Postulaciones recientes' ?>
        </h2>
        <div class="d-flex gap-2">
            <?php if ($id_rol === 1): ?>
                <a href="app.php?page=nueva_postulacion" class="btn btn-usm btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Nueva
                </a>
            <?php endif; ?>
            <a href="app.php?page=listado" class="btn btn-outline-secondary btn-sm">Ver todas</a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recientes)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No hay postulaciones para mostrar.
                <?php if ($id_rol === 1): ?>
                    <br><a href="app.php?page=nueva_postulacion">Crear la primera</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-muted small text-uppercase fw-medium px-3">N° Postulación</th>
                        <th class="text-muted small text-uppercase fw-medium">Iniciativa</th>
                        <th class="text-muted small text-uppercase fw-medium">Empresa</th>
                        <th class="text-muted small text-uppercase fw-medium">Sede</th>
                        <th class="text-muted small text-uppercase fw-medium">Presupuesto</th>
                        <th class="text-muted small text-uppercase fw-medium">Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recientes as $r): ?>
                    <tr>
                        <td class="px-3">
                            <span class="fw-semibold text-muted small"><?= htmlspecialchars($r['numero_postulacion']) ?></span>
                        </td>
                        <td>
                            <span class="fw-medium" style="font-size:13px">
                                <?= htmlspecialchars(mb_strimwidth($r['objetivo'], 0, 50, '…')) ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($r['nombre_empresa']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($r['sede']) ?></td>
                        <td class="fw-medium small">$<?= number_format($r['presupuesto_total'], 0, ',', '.') ?></td>
                        <td><?= badge_estado($r['estado']) ?></td>
                        <td>
                            <a href="app.php?page=ver_postulacion&id=<?= $r['id_postulacion'] ?>"
                               class="btn btn-outline-secondary btn-sm py-0">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
