<?php
// pages/nueva_postulacion.php — ROL 1 exclusivo
requireRol([1]);

$pdo = getDB();

// Catálogos
$tipos_ini = $pdo->query('SELECT * FROM TIPO_INICIATIVA ORDER BY id_tipo_iniciativa')->fetchAll();
$sedes     = $pdo->query('SELECT * FROM SEDE ORDER BY nombre_sede')->fetchAll();
$regiones  = $pdo->query('SELECT * FROM REGION ORDER BY nombre_region')->fetchAll();
$empresas  = $pdo->query('SELECT * FROM EMPRESA ORDER BY nombre_empresa')->fetchAll();
$tamanos   = $pdo->query('SELECT * FROM TAMANO_EMPRESA ORDER BY id_tamano')->fetchAll();
$personas  = $pdo->query(
    'SELECT p.*, tp.descripcion AS tipo, s.nombre_sede AS sede_nombre
     FROM PERSONA p
     JOIN TIPO_PERSONA tp ON p.id_tipo_persona = tp.id_tipo_persona
     JOIN SEDE s ON p.id_sede = s.id_sede
     ORDER BY tp.descripcion, p.apellido'
)->fetchAll();
?>

<!-- Tabs de navegación del formulario -->
<ul class="nav nav-tabs mb-4" id="formTabs">
    <li class="nav-item">
        <a class="nav-link active" href="#" onclick="showTab(1);return false;">
            <span class="badge bg-usm me-1">1</span> Datos generales
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#" onclick="showTab(2);return false;">
            <span class="badge bg-secondary me-1">2</span> Empresa externa
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#" onclick="showTab(3);return false;">
            <span class="badge bg-secondary me-1">3</span> Equipo de trabajo
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#" onclick="showTab(4);return false;">
            <span class="badge bg-secondary me-1">4</span> Cronograma
        </a>
    </li>
</ul>

<form method="POST" action="actions/postulacion.php" id="formPostulacion">
    <input type="hidden" name="accion" value="crear">

    <!-- ── PASO 1: Datos generales ─────────────────────── -->
    <div class="tab-section card border-0 shadow-sm" id="tab1">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-3 pb-2 border-bottom">
                <span class="badge bg-usm me-2">1</span> Datos generales de la postulación
            </h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label label-sm">Tipo de iniciativa <span class="text-danger">*</span></label>
                    <select name="id_tipo_iniciativa" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($tipos_ini as $t): ?>
                            <option value="<?= $t['id_tipo_iniciativa'] ?>"><?= htmlspecialchars($t['descripcion']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label label-sm">Sede / Campus <span class="text-danger">*</span></label>
                    <select name="id_sede" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($sedes as $s): ?>
                            <option value="<?= $s['id_sede'] ?>"><?= htmlspecialchars($s['nombre_sede']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label label-sm">Región de ejecución <span class="text-danger">*</span></label>
                    <select name="id_region_ejecucion" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= $r['id_region'] ?>"><?= htmlspecialchars($r['nombre_region']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label label-sm">Región de impacto <span class="text-danger">*</span></label>
                    <select name="id_region_impacto" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= $r['id_region'] ?>"><?= htmlspecialchars($r['nombre_region']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Fecha inicio <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Fecha término <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_termino" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Presupuesto total ($) <span class="text-danger">*</span></label>
                    <input type="number" name="presupuesto_total" class="form-control form-control-sm"
                           min="1" step="1" placeholder="45000000" required>
                </div>
                <div class="col-12">
                    <label class="form-label label-sm">Objetivo / Nombre de la iniciativa <span class="text-danger">*</span></label>
                    <input type="text" name="objetivo" class="form-control form-control-sm"
                           placeholder="Describe el objetivo principal del proyecto" required maxlength="255">
                </div>
                <div class="col-12">
                    <label class="form-label label-sm">Descripción de soluciones</label>
                    <textarea name="descripcion_soluciones" class="form-control form-control-sm" rows="3"
                              placeholder="Detalla las soluciones técnicas propuestas..." maxlength="255"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label label-sm">Resultados esperados</label>
                    <textarea name="resultados_esperados" class="form-control form-control-sm" rows="3"
                              placeholder="¿Qué resultados medibles se esperan?" maxlength="255"></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            <button type="button" class="btn btn-usm btn-sm" onclick="showTab(2)">
                Siguiente <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    <!-- ── PASO 2: Empresa ─────────────────────────────── -->
    <div class="tab-section card border-0 shadow-sm d-none" id="tab2">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-3 pb-2 border-bottom">
                <span class="badge bg-usm me-2">2</span> Empresa externa
            </h2>
            <div class="mb-3">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="empresaExistente" onchange="toggleEmpresa(this)">
                    <label class="form-check-label small" for="empresaExistente">Seleccionar empresa existente</label>
                </div>
                <!-- Empresa existente -->
                <div id="divEmpresaExistente" class="d-none">
                    <label class="form-label label-sm">Empresa registrada</label>
                    <select name="id_empresa_existente" class="form-select form-select-sm" id="selectEmpresaExistente">
                        <option value="">Seleccionar empresa...</option>
                        <?php foreach ($empresas as $e): ?>
                            <option value="<?= $e['id_empresa'] ?>"><?= htmlspecialchars($e['nombre_empresa']) ?> (<?= htmlspecialchars($e['rut_empresa']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Empresa nueva -->
                <div id="divEmpresaNueva">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label label-sm">RUT empresa <span class="text-danger">*</span></label>
                            <input type="text" name="rut_empresa" class="form-control form-control-sm" placeholder="76.123.456-7">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label label-sm">Nombre empresa <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_empresa" class="form-control form-control-sm" placeholder="TechSolutions SpA">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-sm">Tamaño <span class="text-danger">*</span></label>
                            <select name="id_tamano" class="form-select form-select-sm">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($tamanos as $t): ?>
                                    <option value="<?= $t['id_tamano'] ?>"><?= htmlspecialchars($t['descripcion']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-sm">Convenio marco <span class="text-danger">*</span></label>
                            <select name="convenio_marco" class="form-select form-select-sm">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-sm">Email empresa</label>
                            <input type="email" name="email_empresa" class="form-control form-control-sm" placeholder="contacto@empresa.cl">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showTab(1)">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </button>
            <button type="button" class="btn btn-usm btn-sm" onclick="showTab(3)">
                Siguiente <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    <!-- ── PASO 3: Equipo ──────────────────────────────── -->
    <div class="tab-section card border-0 shadow-sm d-none" id="tab3">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-3 pb-2 border-bottom">
                <span class="badge bg-usm me-2">3</span> Equipo de trabajo
                <small class="text-muted fw-normal ms-2">(mínimo: 3 profesores + 5 estudiantes)</small>
            </h2>

            <div class="table-responsive mb-3">
                <table class="table table-sm align-middle" id="tablaEquipo">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Persona</th>
                            <th class="small">Tipo</th>
                            <th class="small">Rol en el equipo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="equipoBody">
                        <tr id="equipoVacio">
                            <td colspan="4" class="text-muted text-center py-3 small">
                                Agrega integrantes usando el botón de abajo
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Agregar integrante -->
            <div class="row g-2 align-items-end border rounded p-3 bg-light">
                <div class="col-md-5">
                    <label class="form-label label-sm">Persona</label>
                    <select id="selectPersona" class="form-select form-select-sm">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($personas as $per): ?>
                            <option value="<?= $per['id_persona'] ?>"
                                    data-tipo="<?= htmlspecialchars($per['tipo']) ?>"
                                    data-nombre="<?= htmlspecialchars($per['nombre'].' '.$per['apellido']) ?>"
                                    data-sede="<?= htmlspecialchars($per['sede_nombre']) ?>">
                                <?= htmlspecialchars($per['nombre'].' '.$per['apellido']) ?>
                                (<?= htmlspecialchars($per['tipo']) ?> — <?= htmlspecialchars($per['sede_nombre']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label label-sm">Rol en el equipo</label>
                    <input type="text" id="inputRolEquipo" class="form-control form-control-sm"
                           placeholder="Ej: Investigador Principal">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="agregarIntegrante()">
                        <i class="bi bi-plus-lg me-1"></i>Agregar
                    </button>
                </div>
            </div>

            <div id="alertaEquipo" class="alert alert-warning small mt-2 d-none py-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <span id="alertaEquipoTexto"></span>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showTab(2)">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </button>
            <button type="button" class="btn btn-usm btn-sm" onclick="showTab(4)">
                Siguiente <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    <!-- ── PASO 4: Cronograma ──────────────────────────── -->
    <div class="tab-section card border-0 shadow-sm d-none" id="tab4">
        <div class="card-body">
            <h2 class="h6 fw-semibold mb-3 pb-2 border-bottom">
                <span class="badge bg-usm me-2">4</span> Cronograma
                <small class="text-muted fw-normal ms-2">(máximo 36 semanas en total)</small>
            </h2>

            <div class="table-responsive mb-3">
                <table class="table table-sm align-middle" id="tablaCronograma">
                    <thead class="table-light">
                        <tr>
                            <th class="small" style="width:50px">Orden</th>
                            <th class="small">Nombre etapa</th>
                            <th class="small">Descripción / Entregable</th>
                            <th class="small" style="width:100px">Semanas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cronogramaBody">
                        <tr id="cronoVacio">
                            <td colspan="5" class="text-muted text-center py-3 small">
                                Agrega etapas usando el botón de abajo
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="totalSemanas" class="text-muted small mb-3"></div>

            <!-- Agregar etapa -->
            <div class="row g-2 align-items-end border rounded p-3 bg-light">
                <div class="col-md-4">
                    <label class="form-label label-sm">Nombre de la etapa</label>
                    <input type="text" id="inputEtapaNombre" class="form-control form-control-sm"
                           placeholder="Ej: Análisis y diseño">
                </div>
                <div class="col-md-5">
                    <label class="form-label label-sm">Descripción / Entregable</label>
                    <input type="text" id="inputEtapaDesc" class="form-control form-control-sm"
                           placeholder="Ej: Documento de requerimientos">
                </div>
                <div class="col-md-1">
                    <label class="form-label label-sm">Semanas</label>
                    <input type="number" id="inputEtapaSemanas" class="form-control form-control-sm" min="1" value="4">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="agregarEtapa()">
                        <i class="bi bi-plus-lg me-1"></i>Agregar
                    </button>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showTab(3)">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </button>
            <div class="d-flex gap-2">
                <button type="submit" name="guardar_como" value="borrador" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-floppy me-1"></i>Guardar borrador
                </button>
            </div>
        </div>
    </div>

</form>
