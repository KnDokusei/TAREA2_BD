-- ============================================================
-- Parte 3: Script de Creación de Base de Datos
-- CT-USM Postulaciones
-- ============================================================

CREATE DATABASE IF NOT EXISTS ct_usm_postulaciones
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE ct_usm_postulaciones;

-- ============================================================
-- CATÁLOGOS (dominios fijos)
-- ============================================================

CREATE TABLE TIPO_INICIATIVA (
    id_tipo_iniciativa INT          NOT NULL AUTO_INCREMENT,
    descripcion        VARCHAR(30)  NOT NULL,
    CONSTRAINT pk_tipo_iniciativa PRIMARY KEY (id_tipo_iniciativa)
) ENGINE=InnoDB;

CREATE TABLE ESTADO_POSTULACION (
    id_estado   INT         NOT NULL AUTO_INCREMENT,
    descripcion VARCHAR(30) NOT NULL,
    CONSTRAINT pk_estado_postulacion PRIMARY KEY (id_estado)
) ENGINE=InnoDB;

CREATE TABLE TAMANO_EMPRESA (
    id_tamano   INT         NOT NULL AUTO_INCREMENT,
    descripcion VARCHAR(30) NOT NULL,
    CONSTRAINT pk_tamano_empresa PRIMARY KEY (id_tamano)
) ENGINE=InnoDB;

CREATE TABLE TIPO_PERSONA (
    id_tipo_persona INT         NOT NULL AUTO_INCREMENT,
    descripcion     VARCHAR(20) NOT NULL,
    CONSTRAINT pk_tipo_persona PRIMARY KEY (id_tipo_persona)
) ENGINE=InnoDB;

CREATE TABLE REGION (
    id_region    INT         NOT NULL AUTO_INCREMENT,
    nombre_region VARCHAR(50) NOT NULL,
    CONSTRAINT pk_region    PRIMARY KEY (id_region),
    CONSTRAINT uq_region    UNIQUE (nombre_region)
) ENGINE=InnoDB;

CREATE TABLE SEDE (
    id_sede     INT         NOT NULL AUTO_INCREMENT,
    nombre_sede VARCHAR(50) NOT NULL,
    CONSTRAINT pk_sede  PRIMARY KEY (id_sede),
    CONSTRAINT uq_sede  UNIQUE (nombre_sede)
) ENGINE=InnoDB;

-- ============================================================
-- EMPRESA
-- ============================================================

CREATE TABLE EMPRESA (
    id_empresa      INT          NOT NULL AUTO_INCREMENT,
    rut_empresa     VARCHAR(12)  NOT NULL,
    nombre_empresa  VARCHAR(100) NOT NULL,
    id_tamano       INT          NOT NULL,
    convenio_marco  BOOLEAN      NOT NULL,
    email_empresa   VARCHAR(100) NULL,
    telefono        VARCHAR(20)  NULL,
    CONSTRAINT pk_empresa       PRIMARY KEY (id_empresa),
    CONSTRAINT uq_rut_empresa   UNIQUE (rut_empresa),
    CONSTRAINT fk_empresa_tamano
        FOREIGN KEY (id_tamano) REFERENCES TAMANO_EMPRESA(id_tamano)
) ENGINE=InnoDB;

-- ============================================================
-- POSTULACION
-- ============================================================

CREATE TABLE POSTULACION (
    id_postulacion        INT           NOT NULL AUTO_INCREMENT,
    numero_postulacion    VARCHAR(30)   NOT NULL,
    codigo_interno        VARCHAR(30)   NOT NULL,
    fecha_postulacion     DATE          NOT NULL,
    objetivo              VARCHAR(255)  NOT NULL,
    descripcion_soluciones VARCHAR(255) NULL,
    resultados_esperados  VARCHAR(255)  NULL,
    fecha_inicio          DATE          NOT NULL,
    fecha_termino         DATE          NOT NULL,
    presupuesto_total     DECIMAL(15,2) NOT NULL,
    id_tipo_iniciativa    INT           NOT NULL,
    id_estado             INT           NOT NULL,
    id_sede               INT           NOT NULL,
    id_region_ejecucion   INT           NOT NULL,
    id_region_impacto     INT           NOT NULL,
    id_empresa            INT           NOT NULL,
    CONSTRAINT pk_postulacion           PRIMARY KEY (id_postulacion),
    CONSTRAINT uq_numero_postulacion    UNIQUE (numero_postulacion),
    CONSTRAINT uq_codigo_interno        UNIQUE (codigo_interno),
    CONSTRAINT fk_post_tipo_iniciativa
        FOREIGN KEY (id_tipo_iniciativa) REFERENCES TIPO_INICIATIVA(id_tipo_iniciativa),
    CONSTRAINT fk_post_estado
        FOREIGN KEY (id_estado)          REFERENCES ESTADO_POSTULACION(id_estado),
    CONSTRAINT fk_post_sede
        FOREIGN KEY (id_sede)            REFERENCES SEDE(id_sede),
    CONSTRAINT fk_post_region_ejec
        FOREIGN KEY (id_region_ejecucion) REFERENCES REGION(id_region),
    CONSTRAINT fk_post_region_impacto
        FOREIGN KEY (id_region_impacto)   REFERENCES REGION(id_region),
    CONSTRAINT fk_post_empresa
        FOREIGN KEY (id_empresa)          REFERENCES EMPRESA(id_empresa)
) ENGINE=InnoDB;

-- ============================================================
-- ETAPA_CRONOGRAMA
-- ============================================================

CREATE TABLE ETAPA_CRONOGRAMA (
    id_etapa        INT          NOT NULL AUTO_INCREMENT,
    id_postulacion  INT          NOT NULL,
    nombre_etapa    VARCHAR(50)  NOT NULL,
    descripcion     VARCHAR(200) NULL,
    semanas         INT          NOT NULL,
    orden           INT          NOT NULL,
    CONSTRAINT pk_etapa_cronograma PRIMARY KEY (id_etapa),
    CONSTRAINT fk_etapa_postulacion
        FOREIGN KEY (id_postulacion) REFERENCES POSTULACION(id_postulacion)
) ENGINE=InnoDB;

-- ============================================================
-- PERSONA
-- ============================================================

CREATE TABLE PERSONA (
    id_persona      INT          NOT NULL AUTO_INCREMENT,
    rut             VARCHAR(12)  NOT NULL,
    nombre          VARCHAR(50)  NOT NULL,
    apellido        VARCHAR(50)  NOT NULL,
    email           VARCHAR(100) NOT NULL,
    id_tipo_persona INT          NOT NULL,
    id_sede         INT          NOT NULL,
    departamento_area VARCHAR(100) NOT NULL,
    CONSTRAINT pk_persona   PRIMARY KEY (id_persona),
    CONSTRAINT uq_rut       UNIQUE (rut),
    CONSTRAINT fk_persona_tipo
        FOREIGN KEY (id_tipo_persona) REFERENCES TIPO_PERSONA(id_tipo_persona),
    CONSTRAINT fk_persona_sede
        FOREIGN KEY (id_sede)         REFERENCES SEDE(id_sede)
) ENGINE=InnoDB;

-- ============================================================
-- INTEGRANTE_EQUIPO (tabla puente)
-- ============================================================

CREATE TABLE INTEGRANTE_EQUIPO (
    id_postulacion INT         NOT NULL,
    id_persona     INT         NOT NULL,
    rol            VARCHAR(50) NOT NULL,
    CONSTRAINT pk_integrante_equipo PRIMARY KEY (id_postulacion, id_persona),
    CONSTRAINT fk_integrante_postulacion
        FOREIGN KEY (id_postulacion) REFERENCES POSTULACION(id_postulacion),
    CONSTRAINT fk_integrante_persona
        FOREIGN KEY (id_persona)     REFERENCES PERSONA(id_persona)
) ENGINE=InnoDB;

-- ============================================================
-- DOCUMENTO
-- ============================================================

CREATE TABLE DOCUMENTO (
    id_documento   INT          NOT NULL AUTO_INCREMENT,
    id_postulacion INT          NOT NULL,
    nombre_archivo VARCHAR(100) NOT NULL,
    tipo_documento VARCHAR(30)  NULL,
    fecha_carga    DATE         NOT NULL,
    CONSTRAINT pk_documento PRIMARY KEY (id_documento),
    CONSTRAINT fk_documento_postulacion
        FOREIGN KEY (id_postulacion) REFERENCES POSTULACION(id_postulacion)
) ENGINE=InnoDB;

-- Catálogos obligatorios
INSERT INTO TIPO_INICIATIVA (descripcion) VALUES ('Nueva'), ('Existente');

INSERT INTO ESTADO_POSTULACION (descripcion) VALUES 
    ('En Revisión'), ('Aprobada'), ('Rechazada'), ('Cerrada');

INSERT INTO TAMANO_EMPRESA (descripcion) VALUES 
    ('Microempresa'), ('Mediana'), ('Grande');

INSERT INTO TIPO_PERSONA (descripcion) VALUES 
    ('Profesor'), ('Estudiante');

INSERT INTO SEDE (nombre_sede) VALUES 
    ('Campus Casa Central Valparaíso'),
    ('Campus San Joaquín'),
    ('Campus Vitacura'),
    ('Sede Viña del Mar'),
    ('Sede Concepción');

INSERT INTO REGION (nombre_region) VALUES
    ('Región de Arica y Parinacota'),
    ('Región de Tarapacá'),
    ('Región de Antofagasta'),
    ('Región de Atacama'),
    ('Región de Coquimbo'),
    ('Región de Valparaíso'),
    ('Región Metropolitana de Santiago'),
    ('Región del Libertador Gral. Bernardo O\'Higgins'),
    ('Región del Maule'),
    ('Región de Ñuble'),
    ('Región del Biobío'),
    ('Región de La Araucanía'),
    ('Región de Los Ríos'),
    ('Región de Los Lagos'),
    ('Región de Aysén'),
    ('Región de Magallanes');
