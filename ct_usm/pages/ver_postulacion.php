<?php
// pages/ver_postulacion.php
include_once 'includes/badge_estado.php';

$pdo    = getDB();
$id_rol = (int)$_SESSION['id_rol'];
$id_usr = (int)$_SESSION['id_usuario'];
$id_post = (int)($_GET['id'] ?? 0);

if (!$id_post) { setFlash('danger', 'Postulación no encontrada.'); redirect('app.php?page=inicio'); }

// Obtener postulación de la VIEW
$stmt = $pdo->prepare('SELECT * FROM VW_POSTULACIONES_COMPLETAS WHERE id_postulacion = ?');
$stmt->execute([$id_post]);
$p = $stmt->fetch();

if (!$p) { setFlash('danger', 'Postulación no encontrada.'); redirect('app.php?page=inicio'); }

// Equipo
$equipo = $pdo->prepare(
    'SELECT ie.rol, pe.rut, pe.nombre, pe.apellido, pe.email,
            pe.departamento_area, tp.descripcion AS tipo, s.nombre_sede AS sede
     FROM INTEGRANTE_EQUIPO ie
     JOIN PERSONA pe ON ie.id_persona = pe.id_persona
     JOIN TIPO_PERSONA tp ON pe.id_tipo_persona = tp.id_tipo_persona
     JOIN SEDE s ON pe.id_sede = s.id_sede
     WHERE ie.id_postulacion = ?
     ORDER BY tp.descripcion, pe.apellido'
);
$equipo->execute([$id_post]);
$integrantes = $equipo->fetchAll();

// Cronograma
$crono = $pdo->prepare('SELECT * FROM ETAPA_CRONOGRAMA WHERE id_postulacion = ? ORDER BY orden');
$crono->execute([$id_post]);
$etapas = $crono->fetchAll();
$total_semanas = array_sum(array_column($etapas, 'semanas'));

// Validación función SQL
$fnStmt = $pdo->prepare("SELECT fn_cumple_equipo_minimo(?) AS resultado");
$fnStmt->execute([$id_post]);
$cumple_equipo = $fnStmt->fetchColumn();

// Evaluación (si existe)
$evalStmt = $pdo->prepare(
    'SELECT ev.*, CONCAT(u.nombre, " ", u.apellido) AS evaluador
     FROM EVALUACION ev
     JOIN USUARIO u ON ev.id_usuario = u.id_usuario
     WHERE ev.id_postulacion = ?
     ORDER BY ev.fecha_evaluacion DESC LIMIT 1'
);
$evalStmt->execute([$id_post]);
$evaluacion = $evalStmt->fetch();

// Log de estados
$logStmt = $pdo->prepare(
    'SELECT l.*, ea.descripcion AS estado_ant, en.descripcion AS estado_nvo,
            CONCAT(u.nombre, " ", u.apellido) AS usuario_nombre
     FROM LOG_ESTADO_POSTULACION l
     JOIN ESTADO_POSTULACION en ON l.id_estado_nvo = en.id_estado
     LEFT JOIN ESTADO_POSTULACION ea ON l.id_estado_ant = ea.id_estado
     LEFT JOIN USUARIO u ON l.id_usuario = u.id_usuario
     WHERE l.id_postulacion = ?
     ORDER BY l.fecha_cambio DESC'
);
$logStmt->execute([$id_post]);
$log_estados = $logStmt->fetchAll();
?>

<!-- Encabezado -->
<div class="d-flex align-items-start justify-content-between mb-3">
    <div>
        <h2 class="h5 fw-bold mb-1"><?= htmlspecialchars($p['objetivo']) ?></h2>
        <span class="text-muted small"><?= htmlspecialchars($p['numero_postulacion']) ?></span>
        &bull;
        <span class="text-muted small"><?= htmlspecialchars($p['codigo_interno']) ?></span>
        &nbsp; <?= badge_estado($p['estado']) ?>
    </div>
    <div class="d-flex gap-2">
        <?php if ($id_rol === 1 && $p['id_usuario_creador'] == $id_usr && $p['estado'] === 'Borrador'): ?>
            <a href="app.php?page=editar_postulacion&id=<?= $id_post ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Editar
            </a>
            <form method="POST" action="actions/postulacion.php" class="d-inline">
                <input type="hidden" name="accion" value="enviar">
                <input type="hidden" name="id_postulacion" value="<?= $id_post ?>">
                <button type="submit" class="btn btn-success btn-sm"
                        onclick="return confirm('¿Enviar esta postulación?')">
                    <i class="bi bi-send me-1"></i>Enviar
                </button>
            </form>
        <?php endif; ?>
        <?php if ($id_rol === 2 && in_array($p['estado'], ['Enviada','En Revisión'])): ?>
            <button class="btn btn-usm btn-sm" data-bs-toggle="collapse" data-bs-target="#panelEval">
                <i class="bi bi-clipboard-check me-1"></i>
                <?= $evaluacion ? 'Editar evaluación' : 'Registrar evaluación' ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <!-- Col izquierda -->
    <div class="col-lg-8">

        <!-- Datos generales -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h3 class="h6 fw-semibold mb-0"><i class="bi bi-info-circle me-2"></i>Datos generales</h3>
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    <div class="col-sm-6">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Tipo iniciativa</p>
                        <p class="fw-medium"><?= htmlspecialchars($p['tipo_iniciativa']) ?></p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Sede</p>
                        <p class="fw-medium"><?= htmlspecialchars($p['sede']) ?></p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Región ejecución</p>
                        <p class="fw-medium"><?= htmlspecialchars($p['region_ejecucion']) ?></p>
                    </div>
                    <div class="col-sm-6">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Región impacto</p>
                        <p class="fw-medium"><?= htmlspecialchars($p['region_impacto']) ?></p>
                    </div>
                    <div class="col-sm-4">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Fecha inicio</p>
                        <p class="fw-medium"><?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?></p>
                    </div>
                    <div class="col-sm-4">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Fecha término</p>
                        <p class="fw-medium"><?= date('d/m/Y', strtotime($p['fecha_termino'])) ?></p>
                    </div>
                    <div class="col-sm-4">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Presupuesto total</p>
                        <p class="fw-bold text-success">$<?= number_format($p['presupuesto_total'], 0, ',', '.') ?></p>
                    </div>
                    <?php if ($p['descripcion_soluciones']): ?>
                    <div class="col-12">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Descripción de soluciones</p>
                        <p><?= htmlspecialchars($p['descripcion_soluciones']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['resultados_esperados']): ?>
                    <div class="col-12">
                        <p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Resultados esperados</p>
                        <p class="mb-0"><?= htmlspecialchars($p['resultados_esperados']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Empresa -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h3 class="h6 fw-semibold mb-0"><i class="bi bi-building me-2"></i>Empresa externa</h3>
            </div>
            <div class="card-body small">
                <div class="row g-2">
                    <div class="col-sm-6"><p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Empresa</p><p class="fw-medium"><?= htmlspecialchars($p['nombre_empresa']) ?></p></div>
                    <div class="col-sm-6"><p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">RUT</p><p class="fw-medium"><?= htmlspecialchars($p['rut_empresa']) ?></p></div>
                    <div class="col-sm-6"><p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Tamaño</p><p class="fw-medium"><?= htmlspecialchars($p['tamano_empresa']) ?></p></div>
                    <div class="col-sm-6"><p class="text-muted mb-0" style="font-size:10px;text-transform:uppercase">Convenio marco</p><p class="fw-medium"><?= $p['convenio_marco'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>' ?></p></div>
                </div>
            </div>
        </div>

        <!-- Equipo -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                <h3 class="h6 fw-semibold mb-0"><i class="bi bi-people me-2"></i>Equipo de trabajo</h3>
                <span class="badge <?= $cumple_equipo === 'CUMPLE' ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= $cumple_equipo ?>
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <thead class="table-light"><tr><th>Nombre</th><th>Tipo</th><th>Sede</th><th>Rol</th></tr></thead>
                    <tbody>
                    <?php foreach ($integrantes as $i): ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars($i['nombre'].' '.$i['apellido']) ?><br><span class="text-muted"><?= htmlspecialchars($i['email']) ?></span></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($i['tipo']) ?></span></td>
                            <td class="text-muted"><?= htmlspecialchars($i['sede']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($i['rol']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cronograma -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                <h3 class="h6 fw-semibold mb-0"><i class="bi bi-calendar3 me-2"></i>Cronograma</h3>
                <span class="badge <?= $total_semanas > 36 ? 'bg-danger' : 'bg-secondary' ?>">
                    <?= $total_semanas ?> / 36 semanas
                </span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <thead class="table-light"><tr><th>#</th><th>Etapa</th><th>Descripción</th><th>Semanas</th></tr></thead>
                    <tbody>
                    <?php foreach ($etapas as $e): ?>
                        <tr><td><?= $e['orden'] ?></td><td class="fw-medium"><?= htmlspecialchars($e['nombre_etapa']) ?></td><td class="text-muted"><?= htmlspecialchars($e['descripcion'] ?? '—') ?></td><td><?= $e['semanas'] ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Col derecha -->
    <div class="col-lg-4">

        <!-- Panel evaluación ROL 2 -->
        <?php if ($id_rol === 2 && in_array($p['estado'], ['Enviada','En Revisión'])): ?>
        <div class="collapse <?= !$evaluacion ? 'show' : '' ?> mb-3" id="panelEval">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h3 class="h6 fw-semibold mb-0"><i class="bi bi-clipboard-check me-2"></i>Registrar evaluación</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="actions/evaluacion.php">
                        <input type="hidden" name="id_postulacion" value="<?= $id_post ?>">
                        <div class="mb-2">
                            <label class="form-label label-sm">Puntaje (0–100)</label>
                            <input type="number" name="puntaje" class="form-control form-control-sm" min="0" max="100" step="0.5"
                                   value="<?= $evaluacion['puntaje'] ?? '' ?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label label-sm">Nuevo estado</label>
                            <select name="nuevo_estado" class="form-select form-select-sm" required>
                                <option value="">Seleccionar...</option>
                                <option value="En Revisión">En Revisión</option>
                                <option value="Aprobada">Aprobada</option>
                                <option value="Rechazada">Rechazada</option>
                                <option value="Cerrada">Cerrada</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label label-sm">Comentario</label>
                            <textarea name="comentario" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($evaluacion['comentario'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-usm btn-sm w-100">
                            <i class="bi bi-check-lg me-1"></i>Guardar evaluación
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Evaluación existente -->
        <?php if ($evaluacion): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2">
                <h3 class="h6 fw-semibold mb-0"><i class="bi bi-star me-2"></i>Evaluación</h3>
            </div>
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-usm"><?= number_format($evaluacion['puntaje'], 1) ?></div>
                <div class="text-muted small mb-3">puntos / 100</div>
                <div class="progress mb-3" style="height:8px">
                    <div class="progress-bar bg-usm" style="width:<?= $evaluacion['puntaje'] ?>%"></div>
                </div>
                <p class="small text-muted mb-1">Evaluador: <strong><?= htmlspecialchars($evaluacion['evaluador']) ?></strong></p>
                <p class="small text-muted mb-0"><?= date('d/m/Y H:i', strtotime($evaluacion['fecha_evaluacion'])) ?></p>
                <?php if ($evaluacion['comentario']): ?>
                    <p class="small mt-2 text-start border-top pt-2"><?= htmlspecialchars($evaluacion['comentario']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Historial de estados (Trigger log) -->
        <?php if ($log_estados): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h3 class="h6 fw-semibold mb-0"><i class="bi bi-clock-history me-2"></i>Historial</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                <?php foreach ($log_estados as $log): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between">
                            <span><?= htmlspecialchars($log['estado_ant'] ?? '—') ?> → <strong><?= htmlspecialchars($log['estado_nvo']) ?></strong></span>
                            <span class="text-muted"><?= date('d/m/Y', strtotime($log['fecha_cambio'])) ?></span>
                        </div>
                        <?php if ($log['observacion']): ?>
                            <small class="text-muted"><?= htmlspecialchars($log['observacion']) ?></small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
