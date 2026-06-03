<?php
// pages/admin.php — ROL 3 exclusivo
requireRol([3]);
include_once 'includes/badge_estado.php';

$pdo = getDB();

// Evaluadores
$evaluadores = $pdo->query(
    "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.activo,
            COUNT(ev.id_evaluacion) AS total_evaluaciones
     FROM USUARIO u
     LEFT JOIN EVALUACION ev ON u.id_usuario = ev.id_usuario
     WHERE u.id_rol = 2
     GROUP BY u.id_usuario
     ORDER BY u.nombre"
)->fetchAll();

// Todos los usuarios
$usuarios = $pdo->query(
    "SELECT u.*, r.nombre_rol FROM USUARIO u
     JOIN ROL r ON u.id_rol = r.id_rol
     ORDER BY u.id_rol, u.nombre"
)->fetchAll();

// Postulaciones sin evaluador (Enviada o En Revisión, sin evaluación)
$sin_evaluar = $pdo->query(
    "SELECT v.id_postulacion, v.numero_postulacion, v.objetivo, v.nombre_empresa, v.estado
     FROM VW_POSTULACIONES_COMPLETAS v
     WHERE v.estado IN ('Enviada','En Revisión')
       AND NOT EXISTS (SELECT 1 FROM EVALUACION ev WHERE ev.id_postulacion = v.id_postulacion)
     ORDER BY v.fecha_postulacion ASC"
)->fetchAll();
?>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-evaluadores">Evaluadores</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-asignaciones">Asignaciones</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-usuarios">Todos los usuarios</a></li>
</ul>

<div class="tab-content">

    <!-- EVALUADORES -->
    <div class="tab-pane fade show active" id="tab-evaluadores">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h2 class="h6 fw-semibold mb-0">Evaluadores registrados</h2>
                <button class="btn btn-usm btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoEvaluador">
                    <i class="bi bi-plus-lg me-1"></i>Agregar evaluador
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Nombre</th>
                            <th>Email</th>
                            <th>Evaluaciones</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($evaluadores as $ev): ?>
                        <tr>
                            <td class="px-3 fw-medium"><?= htmlspecialchars($ev['nombre'].' '.$ev['apellido']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($ev['email']) ?></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <?= $ev['total_evaluaciones'] ?> evaluaciones
                                </span>
                            </td>
                            <td>
                                <?php if ($ev['activo']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="actions/admin.php" class="d-inline">
                                    <input type="hidden" name="accion" value="toggle_usuario">
                                    <input type="hidden" name="id_usuario" value="<?= $ev['id_usuario'] ?>">
                                    <input type="hidden" name="activo" value="<?= $ev['activo'] ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-<?= $ev['activo'] ? 'danger' : 'success' ?>">
                                        <?= $ev['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ASIGNACIONES -->
    <div class="tab-pane fade" id="tab-asignaciones">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h2 class="h6 fw-semibold mb-0">
                    Postulaciones sin evaluador asignado
                    <span class="badge bg-warning text-dark ms-1"><?= count($sin_evaluar) ?></span>
                </h2>
            </div>
            <div class="card-body p-0">
                <?php if (empty($sin_evaluar)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-check-all fs-2 d-block mb-2"></i>
                        Todas las postulaciones tienen evaluador asignado.
                    </div>
                <?php else: ?>
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Postulación</th>
                            <th>Empresa</th>
                            <th>Estado</th>
                            <th>Asignar evaluador</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sin_evaluar as $post): ?>
                        <tr>
                            <td class="px-3">
                                <span class="fw-semibold text-muted d-block"><?= htmlspecialchars($post['numero_postulacion']) ?></span>
                                <span><?= htmlspecialchars(mb_strimwidth($post['objetivo'], 0, 45, '…')) ?></span>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($post['nombre_empresa']) ?></td>
                            <td><?= badge_estado($post['estado']) ?></td>
                            <td>
                                <form method="POST" action="actions/admin.php" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="accion" value="asignar_evaluador">
                                    <input type="hidden" name="id_postulacion" value="<?= $post['id_postulacion'] ?>">
                                    <select name="id_evaluador" class="form-select form-select-sm" style="width:auto" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($evaluadores as $ev): if (!$ev['activo']) continue; ?>
                                            <option value="<?= $ev['id_usuario'] ?>">
                                                <?= htmlspecialchars($ev['nombre'].' '.$ev['apellido']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-usm btn-sm">Asignar</button>
                                </form>
                            </td>
                            <td>
                                <a href="app.php?page=ver_postulacion&id=<?= $post['id_postulacion'] ?>"
                                   class="btn btn-outline-secondary btn-sm py-0 px-2">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TODOS LOS USUARIOS -->
    <div class="tab-pane fade" id="tab-usuarios">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="px-3 fw-medium text-muted"><?= htmlspecialchars($u['nombre_usuario']) ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($u['nombre'].' '.$u['apellido']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($u['nombre_rol']) ?></span>
                            </td>
                            <td>
                                <?php if ($u['activo']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactivo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Nuevo evaluador -->
<div class="modal fade" id="modalNuevoEvaluador" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar evaluador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="actions/admin.php">
                <input type="hidden" name="accion" value="crear_evaluador">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label label-sm">Nombre</label>
                            <input type="text" name="nombre" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-sm">Apellido</label>
                            <input type="text" name="apellido" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label label-sm">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-sm">Usuario</label>
                            <input type="text" name="nombre_usuario" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-sm">Contraseña</label>
                            <input type="password" name="password" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-usm btn-sm">Crear evaluador</button>
                </div>
            </form>
        </div>
    </div>
</div>
