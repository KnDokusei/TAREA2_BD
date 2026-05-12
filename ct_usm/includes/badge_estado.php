<?php
// includes/badge_estado.php
if (!function_exists('badge_estado')) {
    function badge_estado(string $estado): string {
        $clase = match($estado) {
            'Aprobada'    => 'bg-success-subtle text-success border border-success-subtle',
            'En Revisión' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'Enviada'     => 'bg-info-subtle text-info border border-info-subtle',
            'Borrador'    => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            'Rechazada'   => 'bg-danger-subtle text-danger border border-danger-subtle',
            'Cerrada'     => 'bg-warning-subtle text-warning border border-warning-subtle',
            default       => 'bg-light text-dark',
        };
        return '<span class="badge rounded-pill ' . $clase . ' fw-medium" style="font-size:11px">'
             . htmlspecialchars($estado) . '</span>';
    }
}
