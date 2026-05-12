<?php
// pages/busqueda.php — Búsqueda avanzada con filtros combinables
include_once 'includes/badge_estado.php';

$pdo    = getDB();
$id_rol = (int)$_SESSION['id_rol'];

// Catálogos para filtros
$regiones  = $pdo->query('SELECT * FROM REGION ORDER BY nombre_region')->fetchAll();
$sedes     = $pdo->query('SELECT * FROM SEDE ORDER BY nombre_sede')->fetchAll();
$estados   = $pdo->query('SELECT * FROM ESTADO_POSTULACION ORDER BY id_estado')->fetchAll();
$tamanos   = $pdo->query('SELECT * FROM TAMANO_EMPRESA ORDER BY id_tamano')->fetchAll();
$tipos_ini = $pdo->query('SELECT * FROM TIPO_INICIATIVA ORDER BY id_tipo_iniciativa')->fetchAll();
$evaluadores = $pdo->query(
    "SELECT u.id_usuario, CONCAT(u.nombre,' ',u.apellido) AS nombre_completo
     FROM USUARIO u WHERE u.id_rol = 2 AND u.activo = 1 ORDER BY u.nombre"
)->fetchAll();

// Filtros recibidos
$f = [
    'q'              => trim($_GET['q']            ?? ''),
    'id_region_ejec' => (int)($_GET['id_region_ejec'] ?? 0),
    'id_region_imp'  => (int)($_GET['id_region_imp']  ?? 0),
    'id_sede'        => (int)($_GET['id_sede']         ?? 0),
    'id_tipo_ini'    => (int)($_GET['id_tipo_ini']     ?? 0),
    'id_tamano'      => (int)($_GET['id_tamano']       ?? 0),
    'convenio'       => $_GET['convenio']              ?? '',
    'id_estado'      => (int)($_GET['id_estado']       ?? 0),
    'id_evaluador'   => (int)($_GET['id_evaluador']    ?? 0),
];

$resultados  = [];
$buscado     = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && array_filter($f)) {
    $buscado = true;
    $where   = [];
    $params  = [];

    if ($f['q']) {
        $where[]  = '(v.numero_postulacion LIKE ? OR v.objetivo LIKE ? OR v.nombre_empresa LIKE ? OR v.codigo_interno LIKE ?)';
        $like = '%' . $f['q'] . '%';
        array_push($params, $like, $like, $like, $like);
    }
    if ($f['id_region_ejec']) { $where[] = 'v.id_region_ejecucion = ?'; $params[] = $f['id_region_ejec']; }
    if ($f['id_region_imp'])  { $where[] = 'v.id_region_impacto = ?';   $params[] = $f['id_region_imp'];  }
    if ($f['id_sede'])        { $where[] = 'v.id_sede = ?';             $params[] = $f['id_sede'];         }
    if ($f['id_tipo_ini'])    { $where[] = 'v.id_tipo_iniciativa = ?'; $params[] = $f['id_tipo_ini']; }
    if ($f['id_tamano'])      { $where[] = 'EXISTS(SELECT 1 FROM EMPRESA emp2 JOIN TAMANO_EMPRESA te2 ON emp2.id_tamano=te2.id_tamano WHERE emp2.id_empresa=v.id_empresa AND te2.id_tamano=?)'; $params[] = $f['id_tamano']; }
    if ($f['convenio'] !== '') { $where[] = 'v.convenio_marco = ?'; $params[] = (int)$f['convenio']; }
    if ($f['id_estado'])      { $where[] = 'v.id_estado = ?'; $params[] = $f['id_estado']; }
    if ($f['id_evaluador'])   { $where[] = 'EXISTS(SELECT 1 FROM EVALUACION ev2 WHERE ev2.id_postulacion=v.id_postulacion AND ev2.id_usuario=?)'; $params[] = $f['id_evaluador']; }

    // ROL 1 solo ve sus propias borradores + todas las gestionadas
    if ($id_rol === 1) {
        $where[] = "(v.id_usuario_creador = ? OR v.estado != 'Borrador')";
        $params[] = (int)$_SESSION['id_usuario'];
    }

    $sql = 'SELECT * FROM VW_POSTULACIONES_COMPLETAS v';
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY v.fecha_postulacion DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll();
}
?>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-semibold mb-0"><i class="bi bi-funnel me-2"></i>Filtros de búsqueda</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="app.php">
            <input type="hidden" name="page" value="busqueda">
            <div class="row g-2">
                <div class="col-md-12">
                    <label class="form-label label-sm">Texto libre (código, iniciativa, empresa)</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Buscar por texto..." value="<?= htmlspecialchars($f['q']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Región de ejecución</label>
                    <select name="id_region_ejec" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= $r['id_region'] ?>" <?= $f['id_region_ejec'] == $r['id_region'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nombre_region']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Región de impacto</label>
                    <select name="id_region_imp" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= $r['id_region'] ?>" <?= $f['id_region_imp'] == $r['id_region'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nombre_region']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Sede / Campus</label>
                    <select name="id_sede" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s['id_sede'] ?>" <?= $f['id_sede'] == $s['id_sede'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nombre_sede']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label label-sm">Tipo de iniciativa</label>
                    <select name="id_tipo_ini" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($tipos_ini as $t): ?>
                            <option value="<?= $t['id_tipo_iniciativa'] ?>" <?= $f['id_tipo_ini'] == $t['id_tipo_iniciativa'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label label-sm">Tamaño empresa</label>
                    <select name="id_tamano" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($tamanos as $t): ?>
                            <option value="<?= $t['id_tamano'] ?>" <?= $f['id_tamano'] == $t['id_tamano'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label label-sm">Convenio marco</label>
                    <select name="convenio" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="1" <?= $f['convenio'] === '1' ? 'selected' : '' ?>>Sí</option>
                        <option value="0" <?= $f['convenio'] === '0' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label label-sm">Estado</label>
                    <select name="id_estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($estados as $e): ?>
                            <option value="<?= $e['id_estado'] ?>" <?= $f['id_estado'] == $e['id_estado'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label label-sm">Evaluador asignado</label>
                    <select name="id_evaluador" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($evaluadores as $ev): ?>
                            <option value="<?= $ev['id_usuario'] ?>" <?= $f['id_evaluador'] == $ev['id_usuario'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-usm btn-sm">
                    <i class="bi bi-search me-1"></i>Buscar
                </button>
                <a href="app.php?page=busqueda" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg me-1"></i>Limpiar filtros
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Resultados -->
<?php if ($buscado): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-2">
        <h3 class="h6 fw-semibold mb-0">
            Resultados
            <span class="badge bg-secondary ms-1"><?= count($resultados) ?></span>
        </h3>
    </div>
    <div class="card-body p-0">
        <?php if (empty($resultados)): ?>
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-search fs-2 d-block mb-2"></i>
                No se encontraron postulaciones con esos criterios.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Código</th>
                        <th>Iniciativa</th>
                        <th>Empresa</th>
                        <th>Sede</th>
                        <th>Reg. ejec.</th>
                        <th>Reg. impacto</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resultados as $r): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-muted"><?= htmlspecialchars($r['numero_postulacion']) ?></td>
                        <td style="max-width:180px" class="fw-medium"><?= htmlspecialchars(mb_strimwidth($r['objetivo'], 0, 50, '…')) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($r['nombre_empresa']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($r['sede']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($r['region_ejecucion']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($r['region_impacto']) ?></td>
                        <td><?= badge_estado($r['estado']) ?></td>
                        <td>
                            <a href="app.php?page=ver_postulacion&id=<?= $r['id_postulacion'] ?>"
                               class="btn btn-outline-secondary btn-sm py-0 px-2">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
