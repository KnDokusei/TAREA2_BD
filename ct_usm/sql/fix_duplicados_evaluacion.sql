-- ============================================================
-- SCRIPT DE LIMPIEZA: Eliminar evaluaciones duplicadas
-- ============================================================
-- Ejecutar UNA SOLA VEZ en phpMyAdmin sobre la BD existente
-- cuando ya ocurrió el bug de duplicación.
--
-- Qué hace:
--   1. Mantiene solo la evaluación más reciente (MAX id_evaluacion)
--      por combinación (id_postulacion, id_usuario).
--   2. Elimina todas las filas duplicadas anteriores.
--   3. Agrega el UNIQUE constraint si no existe, para que
--      el error no vuelva a ocurrir nunca más.
-- ============================================================

USE ct_usm_postulaciones;

-- Paso 1: Borrar duplicados — conservar solo el más reciente
DELETE ev
FROM EVALUACION ev
INNER JOIN (
    -- Obtener el id_evaluacion más reciente por (postulación, usuario)
    SELECT MAX(id_evaluacion) AS max_id, id_postulacion, id_usuario
    FROM EVALUACION
    GROUP BY id_postulacion, id_usuario
) keep_rows
    ON ev.id_postulacion = keep_rows.id_postulacion
   AND ev.id_usuario     = keep_rows.id_usuario
   AND ev.id_evaluacion  <> keep_rows.max_id;

-- Paso 2: Agregar UNIQUE constraint si no existe
-- (No falla si ya existe gracias a la verificación previa)
SET @constraint_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = 'ct_usm_postulaciones'
      AND TABLE_NAME   = 'EVALUACION'
      AND CONSTRAINT_NAME = 'uq_eval_post_usuario'
);

-- Ejecutar solo si no existe el constraint
SET @sql = IF(
    @constraint_exists = 0,
    'ALTER TABLE EVALUACION ADD CONSTRAINT uq_eval_post_usuario UNIQUE (id_postulacion, id_usuario)',
    'SELECT "Constraint uq_eval_post_usuario ya existe, nada que hacer" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar resultado
SELECT
    id_postulacion,
    id_usuario,
    COUNT(*) AS total_filas
FROM EVALUACION
GROUP BY id_postulacion, id_usuario
HAVING total_filas > 1;
-- Si esta query no devuelve filas: la limpieza fue exitosa.
