-- ============================================================
-- VISTAS SQL PARA POWER BI — CT-USM Postulaciones
-- Ejecutar DESPUÉS de dml_caso_masivo.sql
-- ============================================================

USE ct_usm_postulaciones;

-- ============================================================
-- VISTA 1: vw_postulaciones_gestion
-- Propósito: Dashboard 1 — Control general de postulaciones y presupuesto
-- ============================================================

CREATE OR REPLACE VIEW vw_postulaciones_gestion AS
SELECT
    p.id_postulacion,
    p.numero_postulacion                        AS postulacion_numero,
    p.codigo_interno,
    p.fecha_postulacion,
    YEAR(p.fecha_postulacion)                   AS anio,
    MONTH(p.fecha_postulacion)                  AS mes_num,
    DATE_FORMAT(p.fecha_postulacion, '%Y-%m')   AS anio_mes,
    MONTHNAME(p.fecha_postulacion)              AS mes_nombre,
    p.objetivo                                  AS nombre_iniciativa,
    p.presupuesto_total,
    p.fecha_inicio,
    p.fecha_termino,
    ti.descripcion                              AS tipo_iniciativa,
    ep.descripcion                              AS estado_postulacion,
    s.id_sede,
    s.nombre_sede                               AS sede,
    re.id_region                                AS id_region_ejecucion,
    re.nombre_region                            AS region_ejecucion,
    ri.id_region                                AS id_region_impacto,
    ri.nombre_region                            AS region_impacto,
    e.id_empresa,
    e.rut_empresa,
    e.nombre_empresa                            AS empresa,
    te.descripcion                              AS tamano_empresa,
    CASE WHEN e.convenio_marco = 1 THEN 'Sí' ELSE 'No' END AS convenio_marco,
    CONCAT(u.nombre, ' ', u.apellido)           AS responsable
FROM POSTULACION p
JOIN TIPO_INICIATIVA    ti ON p.id_tipo_iniciativa  = ti.id_tipo_iniciativa
JOIN ESTADO_POSTULACION ep ON p.id_estado           = ep.id_estado
JOIN SEDE               s  ON p.id_sede             = s.id_sede
JOIN REGION             re ON p.id_region_ejecucion = re.id_region
JOIN REGION             ri ON p.id_region_impacto   = ri.id_region
JOIN EMPRESA            e  ON p.id_empresa          = e.id_empresa
JOIN TAMANO_EMPRESA     te ON e.id_tamano           = te.id_tamano
LEFT JOIN USUARIO       u  ON p.id_usuario_creador  = u.id_usuario;


-- ============================================================
-- VISTA 2: vw_equipo_gestion
-- Propósito: Dashboard 2 — Gestión territorial y perfil de empresas
-- ============================================================

CREATE OR REPLACE VIEW vw_equipo_gestion AS
SELECT
    ie.id_postulacion,
    p.numero_postulacion,
    YEAR(p.fecha_postulacion)                   AS anio,
    MONTH(p.fecha_postulacion)                  AS mes_num,
    ie.id_persona,
    CONCAT(pe.nombre, ' ', pe.apellido)         AS nombre_integrante,
    pe.email                                    AS email_integrante,
    tp.descripcion                              AS tipo_integrante,
    ie.rol                                      AS rol_equipo,
    s.nombre_sede                               AS sede_postulacion,
    sp.nombre_sede                              AS sede_persona,
    e.nombre_empresa                            AS empresa,
    CASE WHEN e.convenio_marco = 1 THEN 'Sí' ELSE 'No' END AS convenio_marco,
    te.descripcion                              AS tamano_empresa,
    r.nombre_region                             AS region_ejecucion,
    ep.descripcion                              AS estado_postulacion
FROM INTEGRANTE_EQUIPO  ie
JOIN POSTULACION        p  ON ie.id_postulacion  = p.id_postulacion
JOIN PERSONA            pe ON ie.id_persona      = pe.id_persona
JOIN TIPO_PERSONA       tp ON pe.id_tipo_persona = tp.id_tipo_persona
JOIN SEDE               s  ON p.id_sede          = s.id_sede
JOIN SEDE               sp ON pe.id_sede         = sp.id_sede
JOIN EMPRESA            e  ON p.id_empresa       = e.id_empresa
JOIN TAMANO_EMPRESA     te ON e.id_tamano        = te.id_tamano
JOIN REGION             r  ON p.id_region_ejecucion = r.id_region
JOIN ESTADO_POSTULACION ep ON p.id_estado        = ep.id_estado;


-- ============================================================
-- VERIFICACIÓN RÁPIDA
-- ============================================================
SELECT COUNT(*) AS total_vw_postulaciones FROM vw_postulaciones_gestion;
SELECT COUNT(*) AS total_vw_equipo        FROM vw_equipo_gestion;