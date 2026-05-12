<?php
// pages/editar_postulacion.php — ROL 1, solo borradores propios
requireRol([1]);

$pdo    = getDB();
$id_usr = (int)$_SESSION['id_usuario'];
$id_post = (int)($_GET['id'] ?? 0);

if (!$id_post) { setFlash('danger', 'ID inválido.'); redirect('app.php?page=mis_postulaciones'); }

$stmt = $pdo->prepare('SELECT * FROM VW_POSTULACIONES_COMPLETAS WHERE id_postulacion = ? AND id_usuario_creador = ?');
$stmt->execute([$id_post, $id_usr]);
$p = $stmt->fetch();

if (!$p) { setFlash('danger', 'Postulación no encontrada o sin permisos.'); redirect('app.php?page=mis_postulaciones'); }
if ($p['estado'] !== 'Borrador') { setFlash('warning', 'Solo se pueden editar postulaciones en estado Borrador.'); redirect('app.php?page=ver_postulacion&id='.$id_post); }

// Catálogos
$tipos_ini = $pdo->query('SELECT * FROM TIPO_INICIATIVA ORDER BY id_tipo_iniciativa')->fetchAll();
$sedes     = $pdo->query('SELECT * FROM SEDE ORDER BY nombre_sede')->fetchAll();
$regiones  = $pdo->query('SELECT * FROM REGION ORDER BY nombre_region')->fetchAll();
$empresas  = $pdo->query('SELECT * FROM EMPRESA ORDER BY nombre_empresa')->fetchAll();
$personas  = $pdo->query(
    'SELECT p.*, tp.descripcion AS tipo, s.nombre_sede AS sede_nombre
     FROM PERSONA p
     JOIN TIPO_PERSONA tp ON p.id_tipo_persona = tp.id_tipo_persona
     JOIN SEDE s ON p.id_sede = s.id_sede
     ORDER BY tp.descripcion, p.apellido'
)->fetchAll();

// Equipo actual
$equipo_actual = $pdo->prepare(
    'SELECT ie.id_persona, ie.rol, pe.nombre, pe.apellido, tp.descripcion AS tipo
     FROM INTEGRANTE_EQUIPO ie
     JOIN PERSONA pe ON ie.id_persona = pe.id_persona
     JOIN TIPO_PERSONA tp ON pe.id_tipo_persona = tp.id_tipo_persona
     WHERE ie.id_postulacion = ? ORDER BY tp.descripcion, pe.apellido'
);
$equipo_actual->execute([$id_post]);
$integrantes = $equipo_actual->fetchAll();

// Cronograma actual
$crono_stmt = $pdo->prepare('SELECT * FROM ETAPA_CRONOGRAMA WHERE id_postulacion = ? ORDER BY orden');
$crono_stmt->execute([$id_post]);
$etapas = $crono_stmt->fetchAll();

// Obtener datos base (no en la view)
$base = $pdo->prepare('SELECT id_tipo_iniciativa FROM POSTULACION WHERE id_postulacion = ?');
$base->execute([$id_post]);
$post_base = $base->fetch();
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="h5 fw-bold mb-0">Editando: <?= htmlspecialchars($p['numero_postulacion']) ?></h2>
    <a href="app.php?page=ver_postulacion&id=<?= $id_post ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Cancelar
    </a>
</div>

<form method="POST" action="actions/postulacion.php">
    <input type="hidden" name="accion" value="editar">
    <input type="hidden" name="id_postulacion" value="<?= $id_post ?>">

    <!-- Datos generales -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h3 class="h6 fw-semibold mb-0"><span class="badge bg-usm me-2">1</span> Datos generales</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label label-sm">Tipo de iniciativa</label>
                    <select name="id_tipo_iniciativa" class="form-select form-select-sm" required>
                        <?php foreach ($tipos_ini as $t): ?>
                            <option value="<?= $t['id_tipo_iniciativa'] ?>"
                                <?= $post_base['id_tipo_iniciativa'] == $t['id_tipo_iniciativa'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label label-sm">Sede / Campus</label>
                    <select name="id_sede" class="form-select form-select-sm" required>
                        <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s['id_sede'] ?>" <?= $p['id_sede'] == $s['id_sede'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nombre_sede']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label label-sm">Región de ejecución</label>
                    <select name="id_region_ejecucion" class="form-select form-select-sm" required>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= $r['id_region'] ?>" <?= $p['id_region_ejecucion'] == $r['id_region'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nombre_region']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label label-sm">Región de impacto</label>
                    <select name="id_region_impacto" class="form-select form-select-sm" required>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= $r['id_region'] ?>" <?= $p['id_region_impacto'] == $r['id_region'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nombre_region']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                           value="<?= $p['fecha_inicio'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Fecha término</label>
                    <input type="date" name="fecha_termino" class="form-control form-control-sm"
                           value="<?= $p['fecha_termino'] ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Presupuesto total ($)</label>
                    <input type="number" name="presupuesto_total" class="form-control form-control-sm"
                           value="<?= $p['presupuesto_total'] ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label label-sm">Objetivo / Nombre iniciativa</label>
                    <input type="text" name="objetivo" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($p['objetivo']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label label-sm">Descripción de soluciones</label>
                    <textarea name="descripcion_soluciones" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($p['descripcion_soluciones'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label label-sm">Resultados esperados</label>
                    <textarea name="resultados_esperados" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($p['resultados_esperados'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Empresa -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h3 class="h6 fw-semibold mb-0"><span class="badge bg-usm me-2">2</span> Empresa externa</h3>
        </div>
        <div class="card-body">
            <label class="form-label label-sm">Empresa asociada</label>
            <select name="id_empresa" class="form-select form-select-sm" required>
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id_empresa'] ?>" <?= $p['id_empresa'] == $e['id_empresa'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nombre_empresa']) ?> (<?= htmlspecialchars($e['rut_empresa']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="app.php?page=ver_postulacion&id=<?= $id_post ?>" class="btn btn-outline-secondary btn-sm">Cancelar</a>
        <button type="submit" class="btn btn-usm btn-sm">
            <i class="bi bi-floppy me-1"></i>Guardar cambios
        </button>
    </div>
</form>

<!-- Equipo y cronograma se editan con acciones individuales -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <h3 class="h6 fw-semibold mb-0"><span class="badge bg-usm me-2">3</span> Equipo de trabajo</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 small">
            <thead class="table-light"><tr><th class="px-3">Nombre</th><th>Tipo</th><th>Rol</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($integrantes as $i): ?>
                <tr>
                    <td class="px-3 fw-medium"><?= htmlspecialchars($i['nombre'].' '.$i['apellido']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($i['tipo']) ?></span></td>
                    <td class="text-muted"><?= htmlspecialchars($i['rol']) ?></td>
                    <td>
                        <form method="POST" action="actions/equipo.php" class="d-inline">
                            <input type="hidden" name="accion" value="quitar">
                            <input type="hidden" name="id_postulacion" value="<?= $id_post ?>">
                            <input type="hidden" name="id_persona" value="<?= $i['id_persona'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2"
                                    onclick="return confirm('¿Quitar a esta persona del equipo?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        <form method="POST" action="actions/equipo.php" class="row g-2 align-items-end">
            <input type="hidden" name="accion" value="agregar">
            <input type="hidden" name="id_postulacion" value="<?= $id_post ?>">
            <div class="col-md-6">
                <label class="form-label label-sm">Agregar persona</label>
                <select name="id_persona" class="form-select form-select-sm" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($personas as $per): ?>
                        <option value="<?= $per['id_persona'] ?>">
                            <?= htmlspecialchars($per['nombre'].' '.$per['apellido']) ?>
                            (<?= htmlspecialchars($per['tipo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label label-sm">Rol</label>
                <input type="text" name="rol" class="form-control form-control-sm"
                       placeholder="Ej: Investigador Principal" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-plus-lg"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cronograma -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-2">
        <h3 class="h6 fw-semibold mb-0"><span class="badge bg-usm me-2">4</span> Cronograma</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 small">
            <thead class="table-light"><tr><th class="px-3">#</th><th>Etapa</th><th>Descripción</th><th>Semanas</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($etapas as $e): ?>
                <tr>
                    <td class="px-3"><?= $e['orden'] ?></td>
                    <td class="fw-medium"><?= htmlspecialchars($e['nombre_etapa']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($e['descripcion'] ?? '—') ?></td>
                    <td><?= $e['semanas'] ?></td>
                    <td>
                        <form method="POST" action="actions/cronograma.php" class="d-inline">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_etapa" value="<?= $e['id_etapa'] ?>">
                            <input type="hidden" name="id_postulacion" value="<?= $id_post ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2"
                                    onclick="return confirm('¿Eliminar esta etapa?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        <form method="POST" action="actions/cronograma.php" class="row g-2 align-items-end">
            <input type="hidden" name="accion" value="agregar">
            <input type="hidden" name="id_postulacion" value="<?= $id_post ?>">
            <div class="col-md-4">
                <label class="form-label label-sm">Nombre etapa</label>
                <input type="text" name="nombre_etapa" class="form-control form-control-sm"
                       placeholder="Ej: Análisis y diseño" required>
            </div>
            <div class="col-md-5">
                <label class="form-label label-sm">Descripción / Entregable</label>
                <input type="text" name="descripcion" class="form-control form-control-sm"
                       placeholder="Ej: Documento de requerimientos">
            </div>
            <div class="col-md-1">
                <label class="form-label label-sm">Semanas</label>
                <input type="number" name="semanas" class="form-control form-control-sm" min="1" value="4" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-plus-lg"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>
