-- ============================================================
-- OBJETOS SQL — Tarea 2
-- CT-USM Postulaciones
-- Ejecutar DESPUÉS de ddl_ct_usm.sql, dml_ct_usm.sql
-- y ddl_extension_t2.sql
-- En phpMyAdmin: importar este archivo completo
-- ============================================================

USE ct_usm_postulaciones;

-- ============================================================
-- VIEW: VW_POSTULACIONES_COMPLETAS
-- ============================================================

CREATE OR REPLACE VIEW VW_POSTULACIONES_COMPLETAS AS
SELECT
    p.id_postulacion,
    p.numero_postulacion,
    p.codigo_interno,
    p.fecha_postulacion,
    p.objetivo,
    p.descripcion_soluciones,
    p.resultados_esperados,
    p.fecha_inicio,
    p.fecha_termino,
    p.presupuesto_total,
    ti.id_tipo_iniciativa,
    ti.descripcion                    AS tipo_iniciativa,
    ep.descripcion                    AS estado,
    ep.id_estado,
    s.nombre_sede                     AS sede,
    s.id_sede,
    re.nombre_region                  AS region_ejecucion,
    re.id_region                      AS id_region_ejecucion,
    ri.nombre_region                  AS region_impacto,
    ri.id_region                      AS id_region_impacto,
    e.id_empresa,
    e.nombre_empresa,
    e.rut_empresa,
    te.descripcion                    AS tamano_empresa,
    e.convenio_marco,
    p.id_usuario_creador,
    CONCAT(u.nombre, ' ', u.apellido) AS nombre_responsable,
    u.email                           AS email_responsable
FROM POSTULACION p
JOIN TIPO_INICIATIVA    ti ON p.id_tipo_iniciativa   = ti.id_tipo_iniciativa
JOIN ESTADO_POSTULACION ep ON p.id_estado            = ep.id_estado
JOIN SEDE               s  ON p.id_sede              = s.id_sede
JOIN REGION             re ON p.id_region_ejecucion  = re.id_region
JOIN REGION             ri ON p.id_region_impacto    = ri.id_region
JOIN EMPRESA            e  ON p.id_empresa           = e.id_empresa
JOIN TAMANO_EMPRESA     te ON e.id_tamano            = te.id_tamano
LEFT JOIN USUARIO       u  ON p.id_usuario_creador   = u.id_usuario;

-- ============================================================
-- A partir de aquí se necesita cambiar el delimitador
-- phpMyAdmin lo maneja automáticamente al importar el archivo
-- ============================================================

DELIMITER $$

-- ============================================================
-- FUNCTION: fn_cumple_equipo_minimo
-- ============================================================

DROP FUNCTION IF EXISTS fn_cumple_equipo_minimo$$

CREATE FUNCTION fn_cumple_equipo_minimo(p_id_postulacion INT)
RETURNS VARCHAR(10)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_profesores  INT DEFAULT 0;
    DECLARE v_estudiantes INT DEFAULT 0;

    SELECT
        SUM(CASE WHEN tp.descripcion = 'Profesor'   THEN 1 ELSE 0 END),
        SUM(CASE WHEN tp.descripcion = 'Estudiante' THEN 1 ELSE 0 END)
    INTO v_profesores, v_estudiantes
    FROM INTEGRANTE_EQUIPO ie
    JOIN PERSONA      pe ON ie.id_persona      = pe.id_persona
    JOIN TIPO_PERSONA tp ON pe.id_tipo_persona = tp.id_tipo_persona
    WHERE ie.id_postulacion = p_id_postulacion;

    IF v_profesores >= 3 AND v_estudiantes >= 5 THEN
        RETURN 'CUMPLE';
    ELSE
        RETURN 'NO CUMPLE';
    END IF;
END$$

-- ============================================================
-- STORED PROCEDURE: sp_enviar_postulacion
-- ============================================================

DROP PROCEDURE IF EXISTS sp_enviar_postulacion$$

CREATE PROCEDURE sp_enviar_postulacion(
    IN  p_id_postulacion INT,
    IN  p_id_usuario     INT,
    OUT p_resultado      VARCHAR(200)
)
BEGIN
    DECLARE v_estado_actual INT;
    DECLARE v_cumple_equipo VARCHAR(10);
    DECLARE v_total_semanas INT DEFAULT 0;
    DECLARE v_id_borrador   INT;
    DECLARE v_id_enviada    INT;

    SELECT id_estado INTO v_id_borrador
    FROM ESTADO_POSTULACION WHERE descripcion = 'Borrador' LIMIT 1;

    SELECT id_estado INTO v_id_enviada
    FROM ESTADO_POSTULACION WHERE descripcion = 'Enviada' LIMIT 1;

    SELECT id_estado INTO v_estado_actual
    FROM POSTULACION WHERE id_postulacion = p_id_postulacion;

    IF v_estado_actual IS NULL THEN
        SET p_resultado = 'ERROR: Postulación no encontrada.';
    ELSEIF v_estado_actual != v_id_borrador THEN
        SET p_resultado = 'ERROR: Solo se pueden enviar postulaciones en estado Borrador.';
    ELSE
        SET v_cumple_equipo = fn_cumple_equipo_minimo(p_id_postulacion);

        IF v_cumple_equipo = 'NO CUMPLE' THEN
            SET p_resultado = 'ERROR: El equipo no cumple el mínimo requerido (3 profesores + 5 estudiantes).';
        ELSE
            SELECT COALESCE(SUM(semanas), 0) INTO v_total_semanas
            FROM ETAPA_CRONOGRAMA
            WHERE id_postulacion = p_id_postulacion;

            IF v_total_semanas = 0 THEN
                SET p_resultado = 'ERROR: La postulación no tiene etapas de cronograma registradas.';
            ELSEIF v_total_semanas > 36 THEN
                SET p_resultado = CONCAT('ERROR: El cronograma excede el máximo de 36 semanas (registradas: ', v_total_semanas, ').');
            ELSE
                UPDATE POSTULACION
                SET id_estado = v_id_enviada
                WHERE id_postulacion = p_id_postulacion;

                SET p_resultado = CONCAT('OK: Postulación enviada exitosamente. Total semanas: ', v_total_semanas, '.');
            END IF;
        END IF;
    END IF;
END$$

-- ============================================================
-- TRIGGER: trg_log_cambio_estado
-- ============================================================

DROP TRIGGER IF EXISTS trg_log_cambio_estado$$

CREATE TRIGGER trg_log_cambio_estado
AFTER UPDATE ON POSTULACION
FOR EACH ROW
BEGIN
    IF OLD.id_estado != NEW.id_estado THEN
        INSERT INTO LOG_ESTADO_POSTULACION
            (id_postulacion, id_estado_ant, id_estado_nvo, fecha_cambio)
        VALUES
            (NEW.id_postulacion, OLD.id_estado, NEW.id_estado, NOW());
    END IF;
END$$

DELIMITER ;