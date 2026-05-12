<?php
// ============================================================
// app.php — Router principal de la aplicación
// CT-USM Postulaciones
// ============================================================
session_start();
require_once 'config/db.php';

requireLogin();

// Páginas permitidas por rol
// id_rol: 1=Postulante, 2=Coordinador, 3=Administrador
$paginas_permitidas = [
    'inicio'              => [1, 2, 3],
    'mis_postulaciones'   => [1],
    'nueva_postulacion'   => [1],
    'ver_postulacion'     => [1, 2, 3],
    'editar_postulacion'  => [1],
    'listado'             => [1, 2, 3],
    'busqueda'            => [1, 2, 3],
    'evaluaciones'        => [2, 3],
    'admin'               => [3],
];

$page    = $_GET['page'] ?? 'inicio';
$id_rol  = (int)$_SESSION['id_rol'];

// Validar que la página exista y que el rol tenga acceso
if (!array_key_exists($page, $paginas_permitidas)) {
    $page = 'inicio';
}
if (!in_array($id_rol, $paginas_permitidas[$page], true)) {
    setFlash('danger', 'No tienes permisos para acceder a esa sección.');
    $page = 'inicio';
}

$page_file = "pages/{$page}.php";
if (!file_exists($page_file)) {
    $page_file = 'pages/inicio.php';
    $page = 'inicio';
}

// Títulos de las secciones
$titulos = [
    'inicio'             => ['Inicio', 'Resumen general del sistema'],
    'mis_postulaciones'  => ['Mis postulaciones', 'Postulaciones donde eres responsable'],
    'nueva_postulacion'  => ['Crear postulación', 'Registrar nueva iniciativa CT-USM'],
    'ver_postulacion'    => ['Detalle de postulación', 'Información completa'],
    'editar_postulacion' => ['Editar postulación', 'Modificar postulación en borrador'],
    'listado'            => ['Listado general', 'Todas las postulaciones gestionadas'],
    'busqueda'           => ['Búsqueda avanzada', 'Filtrar postulaciones con criterios combinados'],
    'evaluaciones'       => ['Evaluaciones', 'Revisar y registrar evaluaciones'],
    'admin'              => ['Gestión de evaluadores', 'Administrar evaluadores y asignaciones'],
];

$titulo_pagina = $titulos[$page][0] ?? 'CT-USM';
$subtitulo     = $titulos[$page][1] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CT-USM — <?= htmlspecialchars($titulo_pagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<div class="d-flex" id="wrapper">

    <!-- ====== SIDEBAR ====== -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- ====== CONTENIDO PRINCIPAL ====== -->
    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column">

        <!-- Topbar -->
        <nav class="topbar navbar navbar-expand px-4">
            <div>
                <h1 class="topbar-title mb-0"><?= htmlspecialchars($titulo_pagina) ?></h1>
                <p class="topbar-sub mb-0"><?= htmlspecialchars($subtitulo) ?></p>
            </div>
            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Barra de búsqueda rápida -->
                <form class="d-flex" method="GET" action="app.php">
                    <input type="hidden" name="page" value="listado">
                    <div class="input-group input-group-sm search-topbar">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 bg-light"
                               placeholder="Buscar postulación..."
                               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    </div>
                </form>
                <!-- Usuario -->
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown">
                        <span class="avatar-sm"><?= strtoupper(substr($_SESSION['nombre'], 0, 1) . substr($_SESSION['apellido'], 0, 1)) ?></span>
                        <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($_SESSION['rol_nombre']) ?></span></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="actions/auth.php?accion=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Contenido de la página -->
        <main class="p-4 flex-grow-1">
            <?= getFlash() ?>
            <?php include $page_file; ?>
        </main>

    </div><!-- /page-content-wrapper -->
</div><!-- /wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>
