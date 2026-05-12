<?php
// pages/mis_postulaciones.php — ROL 1 exclusivo
requireRol([1]);
include_once 'includes/badge_estado.php';

$pdo    = getDB();
$id_usr = (int)$_SESSION['id_usuario'];

$stmt = $pdo->prepare(
    'SELECT * FROM VW_POSTULACIONES_COMPLETAS
     WHERE id_usuario_creador = ?
     ORDER BY fecha_postulacion DESC'
);
$stmt->execute([$id_usr]);
$postulaciones = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted small mb-0">
        <?= count($postulaciones) ?> postulación(es) registrada(s) a tu nombre
    </p>
    <a href="app.php?page=nueva_postulacion" class="btn btn-usm btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Crear nueva
    </a>
</div>

<?php if (empty($postulaciones)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-folder2-open fs-1 d-block mb-3"></i>
            <p class="mb-1">Aún no tienes postulaciones.</p>
            <a href="app.php?page=nueva_postulacion" class="btn btn-usm btn-sm mt-2">
                Crear mi primera postulación
            </a>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($postulaciones as $p): ?>
        <?php
        // Calcular equipo usando FUNCTION SQL
        $fnStmt = $pdo->prepare("SELECT fn_cumple_equipo_minimo(?) AS resultado");
        $fnStmt->execute([$p['id_postulacion']]);
        $cumple = $fnStmt->fetchColumn();
        ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-1 text-muted small fw-semibold">
                        <?= htmlspecialchars($p['numero_postulacion']) ?>
                    </div>
                    <div class="col-md-4">
                        <p class="fw-medium mb-1"><?= htmlspecialchars($p['objetivo']) ?></p>
                        <p class="text-muted small mb-0">
                            <?= htmlspecialchars($p['nombre_empresa']) ?>
                            &bull; <?= htmlspecialchars($p['sede']) ?>
                            &bull; <?= htmlspecialchars($p['region_ejecucion']) ?>
                        </p>
                    </div>
                    <div class="col-md-2 text-muted small">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('d/m/Y', strtotime($p['fecha_postulacion'])) ?>
                    </div>
                    <div class="col-md-2 fw-medium small">
                        $<?= number_format($p['presupuesto_total'], 0, ',', '.') ?>
                    </div>
                    <div class="col-md-1">
                        <?= badge_estado($p['estado']) ?>
                    </div>
                    <div class="col-md-1">
                        <?php if ($cumple === 'CUMPLE'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:10px">
                                <i class="bi bi-people"></i> OK
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:10px"
                                  title="No cumple mínimo: 3 profesores + 5 estudiantes">
                                <i class="bi bi-exclamation-triangle"></i> Equipo
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-1 text-end">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                Acciones
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="app.php?page=ver_postulacion&id=<?= $p['id_postulacion'] ?>">
                                        <i class="bi bi-eye me-2"></i>Ver detalle
                                    </a>
                                </li>
                                <?php if ($p['estado'] === 'Borrador'): ?>
                                <li>
                                    <a class="dropdown-item" href="app.php?page=editar_postulacion&id=<?= $p['id_postulacion'] ?>">
                                        <i class="bi bi-pencil me-2"></i>Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="actions/postulacion.php" class="d-inline">
                                        <input type="hidden" name="accion" value="enviar">
                                        <input type="hidden" name="id_postulacion" value="<?= $p['id_postulacion'] ?>">
                                        <button type="submit" class="dropdown-item text-success"
                                                onclick="return confirm('¿Enviar esta postulación? No podrás editarla después.')">
                                            <i class="bi bi-send me-2"></i>Enviar postulación
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form method="POST" action="actions/postulacion.php" class="d-inline">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_postulacion" value="<?= $p['id_postulacion'] ?>">
                                        <button type="submit" class="dropdown-item text-danger"
                                                onclick="return confirm('¿Eliminar esta postulación? Esta acción no se puede deshacer.')">
                                            <i class="bi bi-trash me-2"></i>Eliminar borrador
                                        </button>
                                    </form>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
