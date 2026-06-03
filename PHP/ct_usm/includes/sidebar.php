<?php
// includes/sidebar.php
$id_rol = (int)$_SESSION['id_rol'];
$page_actual = $page ?? 'inicio';

$nav_items = [
    ['page' => 'inicio',            'icon' => 'bi-house',          'label' => 'Inicio',              'roles' => [1,2,3]],
    ['page' => 'mis_postulaciones', 'icon' => 'bi-folder2-open',   'label' => 'Mis postulaciones',   'roles' => [1]],
    ['page' => 'nueva_postulacion', 'icon' => 'bi-plus-circle',    'label' => 'Crear postulación',   'roles' => [1]],
    ['page' => 'listado',           'icon' => 'bi-list-ul',        'label' => 'Listado general',     'roles' => [1,2,3]],
    ['page' => 'busqueda',          'icon' => 'bi-funnel',         'label' => 'Búsqueda avanzada',   'roles' => [1,2,3]],
    ['page' => 'evaluaciones',      'icon' => 'bi-clipboard-check','label' => 'Evaluaciones',        'roles' => [2,3]],
    ['page' => 'admin',             'icon' => 'bi-people',         'label' => 'Gestión evaluadores', 'roles' => [3]],
];
?>
<nav id="sidebar" class="sidebar d-flex flex-column">

    <!-- Brand -->
    <div class="sidebar-brand px-3 py-3 border-bottom border-secondary border-opacity-25">
        <div class="d-flex align-items-center gap-2">
            <div class="brand-icon">
                <span class="fw-bold text-warning small">USM</span>
            </div>
            <div>
                <div class="text-white fw-bold small lh-1">CT-USM</div>
                <div class="text-white-50" style="font-size:10px">Postulaciones</div>
            </div>
        </div>
    </div>

    <!-- User info -->
    <div class="px-3 py-2 border-bottom border-secondary border-opacity-25">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar-sm flex-shrink-0">
                <?= strtoupper(substr($_SESSION['nombre'],0,1).substr($_SESSION['apellido'],0,1)) ?>
            </span>
            <div class="overflow-hidden">
                <div class="text-white small fw-medium text-truncate">
                    <?= htmlspecialchars($_SESSION['nombre'].' '.$_SESSION['apellido']) ?>
                </div>
                <div class="text-white-50" style="font-size:10px">
                    <?= htmlspecialchars($_SESSION['rol_nombre']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="flex-grow-1 py-2">
        <div class="px-3 py-1 text-uppercase text-white-50 fw-medium" style="font-size:10px;letter-spacing:.8px">
            Navegación
        </div>
        <?php foreach ($nav_items as $item): ?>
            <?php if (!in_array($id_rol, $item['roles'], true)) continue; ?>
            <a href="app.php?page=<?= $item['page'] ?>"
               class="nav-link-item d-flex align-items-center gap-2 px-3 py-2 <?= ($page_actual === $item['page']) ? 'active' : '' ?>">
                <i class="bi <?= $item['icon'] ?>" style="font-size:15px;flex-shrink:0"></i>
                <span class="small"><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Logout -->
    <div class="px-3 py-2 border-top border-secondary border-opacity-25">
        <a href="actions/auth.php?accion=logout"
           class="d-flex align-items-center gap-2 text-white-50 text-decoration-none small py-1 hover-text-white">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar sesión</span>
        </a>
    </div>

</nav>
