<?php
// pages/inicio.php
include_once 'includes/badge_estado.php';
$pdo    = getDB();
$id_rol = (int)$_SESSION['id_rol'];
$id_usr = (int)$_SESSION['id_usuario'];

// Estadísticas generales
$stats = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN ep.descripcion = 'Aprobada'    THEN 1 ELSE 0 END) AS aprobadas,
        SUM(CASE WHEN ep.descripcion = 'En Revisión' THEN 1 ELSE 0 END) AS en_revision,
        SUM(CASE WHEN ep.descripcion = 'Rechazada'   THEN 1 ELSE 0 END) AS rechazadas,
        SUM(CASE WHEN ep.descripcion = 'Borrador'    THEN 1 ELSE 0 END) AS borradores,
        COALESCE(SUM(p.presupuesto_total), 0)                            AS presupuesto_total
     FROM POSTULACION p
     JOIN ESTADO_POSTULACION ep ON p.id_estado = ep.id_estado"
)->fetch();

// Postulaciones recientes (usa la VIEW)
$where  = ($id_rol === 1) ? 'WHERE v.id_usuario_creador = ' . $id_usr : "WHERE v.estado != 'Borrador'";
$recientes = $pdo->query(
    "SELECT * FROM VW_POSTULACIONES_COMPLETAS v {$where}
     ORDER BY v.fecha_postulacion DESC LIMIT 8"
)->fetchAll();
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
                <p class="stat-sub text-muted small">Iniciativas activas</p>
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
