-- ============================================================
-- Extensión DDL — Tarea 2
-- CT-USM Postulaciones
-- Ejecutar DESPUÉS de ddl_ct_usm.sql y dml_ct_usm.sql
-- ============================================================

USE ct_usm_postulaciones;

-- ============================================================
-- NUEVOS ESTADOS (extender catálogo existente)
-- ============================================================

INSERT INTO ESTADO_POSTULACION (descripcion) VALUES
    ('Borrador'),
    ('Enviada');

-- ============================================================
-- ROL
-- ============================================================

CREATE TABLE ROL (
    id_rol      INT         NOT NULL AUTO_INCREMENT,
    nombre_rol  VARCHAR(50) NOT NULL,
    descripcion VARCHAR(100) NULL,
    CONSTRAINT pk_rol        PRIMARY KEY (id_rol),
    CONSTRAINT uq_nombre_rol UNIQUE (nombre_rol)
) ENGINE=InnoDB;

INSERT INTO ROL (nombre_rol, descripcion) VALUES
    ('Postulante',    'Responsable académico: crea y envía postulaciones'),
    ('Coordinador',   'Evaluador CT-USM: revisa y registra evaluación'),
    ('Administrador', 'Administrador CT-USM: gestiona evaluadores y asignaciones');

-- ============================================================
-- USUARIO
-- ============================================================

CREATE TABLE USUARIO (
    id_usuario     INT          NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(50)  NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    nombre         VARCHAR(50)  NOT NULL,
    apellido       VARCHAR(50)  NOT NULL,
    email          VARCHAR(100) NOT NULL,
    id_rol         INT          NOT NULL,
    id_persona     INT          NULL,
    activo         BOOLEAN      NOT NULL DEFAULT TRUE,
    CONSTRAINT pk_usuario        PRIMARY KEY (id_usuario),
    CONSTRAINT uq_nombre_usuario UNIQUE (nombre_usuario),
    CONSTRAINT uq_email_usuario  UNIQUE (email),
    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (id_rol)    REFERENCES ROL(id_rol),
    CONSTRAINT fk_usuario_persona
        FOREIGN KEY (id_persona) REFERENCES PERSONA(id_persona)
) ENGINE=InnoDB;

-- ============================================================
-- EXTENSIÓN DE POSTULACION
-- ============================================================

ALTER TABLE POSTULACION
    ADD COLUMN id_usuario_creador INT NULL AFTER id_empresa,
    ADD CONSTRAINT fk_post_usuario
        FOREIGN KEY (id_usuario_creador) REFERENCES USUARIO(id_usuario);

-- ============================================================
-- LOG_ESTADO_POSTULACION
-- ============================================================

CREATE TABLE LOG_ESTADO_POSTULACION (
    id_log         INT          NOT NULL AUTO_INCREMENT,
    id_postulacion INT          NOT NULL,
    id_estado_ant  INT          NULL,
    id_estado_nvo  INT          NOT NULL,
    id_usuario     INT          NULL,
    fecha_cambio   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observacion    VARCHAR(255) NULL,
    CONSTRAINT pk_log_estado_post    PRIMARY KEY (id_log),
    CONSTRAINT fk_log_postulacion
        FOREIGN KEY (id_postulacion) REFERENCES POSTULACION(id_postulacion),
    CONSTRAINT fk_log_estado_ant
        FOREIGN KEY (id_estado_ant)  REFERENCES ESTADO_POSTULACION(id_estado),
    CONSTRAINT fk_log_estado_nvo
        FOREIGN KEY (id_estado_nvo)  REFERENCES ESTADO_POSTULACION(id_estado),
    CONSTRAINT fk_log_usuario
        FOREIGN KEY (id_usuario)     REFERENCES USUARIO(id_usuario)
) ENGINE=InnoDB;

-- ============================================================
-- EVALUACION
-- UNIQUE KEY (id_postulacion, id_usuario) → garantiza que
-- cada evaluador solo pueda tener UNA evaluación por postulación.
-- Sin este constraint, ON DUPLICATE KEY y el SELECT+UPDATE/INSERT
-- del PHP no tienen efecto y se generan filas duplicadas.
-- ============================================================

CREATE TABLE EVALUACION (
    id_evaluacion    INT           NOT NULL AUTO_INCREMENT,
    id_postulacion   INT           NOT NULL,
    id_usuario       INT           NOT NULL,
    puntaje          DECIMAL(5,2)  NULL,
    comentario       TEXT          NULL,
    fecha_evaluacion DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_evaluacion            PRIMARY KEY (id_evaluacion),
    CONSTRAINT uq_eval_post_usuario     UNIQUE (id_postulacion, id_usuario),
    CONSTRAINT fk_eval_postulacion
        FOREIGN KEY (id_postulacion) REFERENCES POSTULACION(id_postulacion),
    CONSTRAINT fk_eval_usuario
        FOREIGN KEY (id_usuario)     REFERENCES USUARIO(id_usuario)
) ENGINE=InnoDB;

-- ============================================================
-- DATOS DE PRUEBA — USUARIOS
-- ============================================================

INSERT INTO USUARIO (nombre_usuario, password_hash, nombre, apellido, email, id_rol, id_persona) VALUES
    ('carlos.ramirez',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos',   'Ramírez',   'carlos.ramirez@usm.cl',   1, 1),
    ('ana.gonzalez',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana',      'González',  'ana.gonzalez@usm.cl',     1, 2),
    ('pedro.munoz',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro',    'Muñoz',     'pedro.munoz@usm.cl',      1, 3),
    ('maria.torres',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María',    'Torres',    'maria.torres@usm.cl',     1, 4),
    ('jorge.soto',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jorge',    'Soto',      'jorge.soto@usm.cl',       1, 5),
    ('rodrigo.vega',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rodrigo',  'Vega',      'rodrigo.vega@ctusm.cl',   2, NULL),
    ('patricia.leal',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Patricia', 'Leal',      'patricia.leal@ctusm.cl',  2, NULL),
    ('hernan.bravo',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hernán',   'Bravo',     'hernan.bravo@ctusm.cl',   2, NULL),
    ('admin',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin',    'CT-USM',    'admin@ctusm.cl',          3, NULL);

-- ============================================================
-- Vincular postulaciones existentes a sus usuarios creadores
-- ============================================================

UPDATE POSTULACION SET id_usuario_creador = 1 WHERE id_postulacion IN (1, 6);
UPDATE POSTULACION SET id_usuario_creador = 2 WHERE id_postulacion IN (2, 7);
UPDATE POSTULACION SET id_usuario_creador = 3 WHERE id_postulacion IN (3, 8);
UPDATE POSTULACION SET id_usuario_creador = 4 WHERE id_postulacion IN (4, 9);
UPDATE POSTULACION SET id_usuario_creador = 5 WHERE id_postulacion IN (5, 10);

-- ============================================================
-- Datos de prueba — EVALUACION
-- (id_postulacion, id_usuario) es UNIQUE: no puede haber duplicados
-- ============================================================

INSERT INTO EVALUACION (id_postulacion, id_usuario, puntaje, comentario) VALUES
    (1,  6, 87.50, 'Propuesta sólida, buena justificación técnica y presupuesto ajustado.'),
    (2,  6, 72.00, 'Objetivos algo generales, requiere mayor especificidad en resultados.'),
    (3,  7, 91.00, 'Excelente aplicación con métricas claras y cronograma realista.'),
    (5,  7, 88.00, 'Plataforma bien diseñada, buena integración pedagógica.'),
    (8,  8, 83.50, 'Proyecto relevante, requiere ajuste en módulo de optimización.'),
    (4,  7, 79.00, 'Alcance bien definido, documentación técnica puede mejorar.'),
    (6,  6, 85.00, 'Trazabilidad correctamente planteada, equipo multidisciplinario.'),
    (9,  8, 68.00, 'Presupuesto elevado, justificación de AR insuficiente.');

-- ============================================================
-- Datos de prueba — LOG_ESTADO_POSTULACION
-- ============================================================

INSERT INTO LOG_ESTADO_POSTULACION (id_postulacion, id_estado_ant, id_estado_nvo, id_usuario, observacion) VALUES
    (1,  5, 6, 1, 'Postulación enviada por responsable académico'),
    (1,  6, 1, 6, 'Recibida y puesta en revisión'),
    (1,  1, 2, 6, 'Evaluación completada, postulación aprobada'),
    (2,  5, 6, 2, 'Postulación enviada por responsable académico'),
    (2,  6, 1, 6, 'Recibida y puesta en revisión'),
    (3,  5, 6, 3, 'Postulación enviada por responsable académico'),
    (3,  6, 1, 7, 'Recibida y puesta en revisión'),
    (3,  1, 2, 7, 'Evaluación completada, postulación aprobada'),
    (7,  5, 6, 2, 'Postulación enviada por responsable académico'),
    (7,  6, 1, 6, 'Recibida y puesta en revisión'),
    (7,  1, 4, 6, 'Iniciativa cerrada sin financiamiento'),
    (10, 5, 6, 5, 'Postulación enviada por responsable académico'),
    (10, 6, 1, 8, 'Recibida y puesta en revisión'),
    (10, 1, 3, 8, 'Rechazada, no cumple requisitos mínimos de equipo');
