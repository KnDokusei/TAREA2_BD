<?php
// pages/listado.php — Usa VW_POSTULACIONES_COMPLETAS
include_once 'includes/badge_estado.php';

$pdo    = getDB();
$id_rol = (int)$_SESSION['id_rol'];
$id_usr = (int)$_SESSION['id_usuario'];
$q      = trim($_GET['q'] ?? '');

// ROL 1 ve sus postulaciones + las gestionadas (no ajenas en borrador)
// ROL 2 y 3 ven todas excepto borradores ajenos
$params = [];
$where  = [];

if ($id_rol === 1) {
    // Ve: sus propias (cualquier estado) + enviadas/en revisión/aprobadas/rechazadas de todos
    $where[] = "(v.id_usuario_creador = ? OR v.estado NOT IN ('Borrador'))";
    $params[] = $id_usr;
} else {
    $where[] = "v.estado != 'Borrador'";
}

if ($q !== '') {
    $where[] = "(v.numero_postulacion LIKE ? OR v.objetivo LIKE ? OR v.nombre_empresa LIKE ? OR v.codigo_interno LIKE ?)";
    $like = "%{$q}%";
    array_push($params, $like, $like, $like, $like);
}

$sql = 'SELECT * FROM VW_POSTULACIONES_COMPLETAS v';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY v.fecha_postulacion DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$postulaciones = $stmt->fetchAll();
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h2 class="h6 fw-semibold mb-0">
            Postulaciones <span class="badge bg-secondary ms-1"><?= count($postulaciones) ?></span>
        </h2>
        <div class="d-flex gap-2">
            <?php if ($id_rol === 1): ?>
                <a href="app.php?page=nueva_postulacion" class="btn btn-usm btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Nueva
                </a>
            <?php endif; ?>
            <a href="app.php?page=busqueda" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-funnel me-1"></i>Filtros avanzados
            </a>
        </div>
    </div>

    <!-- Búsqueda inline -->
    <?php if ($q): ?>
        <div class="card-body pb-0">
            <div class="alert alert-info py-2 small mb-0">
                Mostrando resultados para: <strong><?= htmlspecialchars($q) ?></strong>
                <a href="app.php?page=listado" class="ms-2">Limpiar</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="card-body p-0">
        <?php if (empty($postulaciones)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                No se encontraron postulaciones<?= $q ? " para «{$q}»" : '' ?>.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 fw-medium text-muted text-uppercase">N° / Código</th>
                        <th class="fw-medium text-muted text-uppercase">Iniciativa</th>
                        <th class="fw-medium text-muted text-uppercase">Empresa</th>
                        <th class="fw-medium text-muted text-uppercase">Sede</th>
                        <th class="fw-medium text-muted text-uppercase">Reg. ejec.</th>
                        <th class="fw-medium text-muted text-uppercase">Presupuesto</th>
                        <th class="fw-medium text-muted text-uppercase">Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($postulaciones as $p): ?>
                    <tr>
                        <td class="px-3">
                            <span class="fw-semibold text-muted d-block"><?= htmlspecialchars($p['numero_postulacion']) ?></span>
                            <span class="text-muted" style="font-size:11px"><?= htmlspecialchars($p['codigo_interno']) ?></span>
                        </td>
                        <td style="max-width:200px">
                            <span class="fw-medium"><?= htmlspecialchars(mb_strimwidth($p['objetivo'], 0, 55, '…')) ?></span>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($p['nombre_empresa']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($p['sede']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($p['region_ejecucion']) ?></td>
                        <td class="fw-medium">$<?= number_format($p['presupuesto_total'], 0, ',', '.') ?></td>
                        <td><?= badge_estado($p['estado']) ?></td>
                        <td>
                            <a href="app.php?page=ver_postulacion&id=<?= $p['id_postulacion'] ?>"
                               class="btn btn-outline-secondary btn-sm py-0 px-2">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($id_rol === 1 && $p['id_usuario_creador'] == $id_usr && $p['estado'] === 'Borrador'): ?>
                                <a href="app.php?page=editar_postulacion&id=<?= $p['id_postulacion'] ?>"
                                   class="btn btn-outline-primary btn-sm py-0 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
