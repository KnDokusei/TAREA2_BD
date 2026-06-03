-- ============================================================
-- TAREA 1 BD - CT USM POSTULACIONES
-- PARTE 4: SCRIPT DML (POBLAMIENTO DE DATOS)
-- ============================================================

USE ct_usm_postulaciones;

-- ============================================================
-- 1. EMPRESAS (6 empresas)
-- Tamaños: 1=Microempresa, 2=Mediana, 3=Grande
-- Convenio mezclado: TRUE/FALSE
-- NOTA: GreenSoft (id=6) no tendrá postulaciones
--       → válida para Query 9 (empresas sin postulaciones)
-- ============================================================

INSERT INTO EMPRESA (rut_empresa, nombre_empresa, id_tamano, convenio_marco, email_empresa, telefono) VALUES
('76.123.456-7', 'TechSolutions SpA',        3, TRUE,  'contacto@techsolutions.cl', '+56221234567'),
('77.234.567-8', 'InnovaChile Ltda.',         2, FALSE, 'info@innovachile.cl',       '+56222345678'),
('78.345.678-9', 'DataCorp S.A.',             1, TRUE,  'admin@datacorp.cl',         '+56223456789'),
('79.456.789-0', 'SoftWave Chile S.A.',       3, FALSE, 'contacto@softwave.cl',      '+56224567890'),
('80.567.890-1', 'EduTech Innovación Ltda.',  2, TRUE,  'soporte@edutech.cl',        '+56225678901'),
('81.678.901-2', 'GreenSoft SpA',             1, FALSE, 'info@greensoft.cl',         '+56226789012');

-- ============================================================
-- 2. PERSONAS (8 profesores + 12 estudiantes = 20 personas)
-- Tipo: 1=Profesor, 2=Estudiante
-- Sede: 1=Casa Central Valparaíso, 2=San Joaquín, 3=Vitacura,
--       4=Viña del Mar, 5=Concepción
-- ============================================================

INSERT INTO PERSONA (rut, nombre, apellido, email, id_tipo_persona, id_sede, departamento_area) VALUES
-- Profesores (id 1-8)
('12.345.678-9', 'Carlos',    'Ramírez',   'carlos.ramirez@usm.cl',     1, 1, 'Departamento de Informática'),
('13.456.789-0', 'Ana',       'González',  'ana.gonzalez@usm.cl',       1, 1, 'Departamento de Electrónica'),
('14.567.890-1', 'Pedro',     'Muñoz',     'pedro.munoz@usm.cl',        1, 2, 'Departamento de Mecánica'),
('15.678.901-2', 'María',     'Torres',    'maria.torres@usm.cl',       1, 2, 'Departamento de Informática'),
('16.789.012-3', 'Jorge',     'Soto',      'jorge.soto@usm.cl',         1, 3, 'Departamento de Industrial'),
('17.890.123-4', 'Claudia',   'Vargas',    'claudia.vargas@usm.cl',     1, 4, 'Departamento de Electrónica'),
('18.901.234-5', 'Roberto',   'Castro',    'roberto.castro@usm.cl',     1, 5, 'Departamento de Mecánica'),
('19.012.345-6', 'Daniela',   'Flores',    'daniela.flores@usm.cl',     1, 1, 'Departamento de Informática'),
-- Estudiantes (id 9-20)
('20.123.456-7', 'Sebastián', 'López',     'sebastian.lopez@usm.cl',    2, 1, 'Ingeniería Civil Informática'),
('21.234.567-8', 'Valentina', 'Martínez',  'valentina.martinez@usm.cl', 2, 1, 'Ingeniería Civil Electrónica'),
('22.345.678-9', 'Diego',     'Hernández', 'diego.hernandez@usm.cl',    2, 2, 'Ingeniería Civil Informática'),
('23.456.789-0', 'Camila',    'Jiménez',   'camila.jimenez@usm.cl',     2, 2, 'Ingeniería Civil Industrial'),
('24.567.890-1', 'Nicolás',   'Morales',   'nicolas.morales@usm.cl',    2, 3, 'Ingeniería Civil Mecánica'),
('25.678.901-2', 'Isabella',  'Díaz',      'isabella.diaz@usm.cl',      2, 3, 'Ingeniería Civil Informática'),
('26.789.012-3', 'Matías',    'Reyes',     'matias.reyes@usm.cl',       2, 4, 'Ingeniería Civil Electrónica'),
('27.890.123-4', 'Sofía',     'Rojas',     'sofia.rojas@usm.cl',        2, 4, 'Ingeniería Civil Informática'),
('28.901.234-5', 'Felipe',    'Navarro',   'felipe.navarro@usm.cl',     2, 5, 'Ingeniería Civil Industrial'),
('29.012.345-6', 'Isidora',   'Pérez',     'isidora.perez@usm.cl',      2, 5, 'Ingeniería Civil Informática'),
('30.123.456-7', 'Tomás',     'Sánchez',   'tomas.sanchez@usm.cl',      2, 1, 'Ingeniería Civil Mecánica'),
('31.234.567-8', 'Catalina',  'Romero',    'catalina.romero@usm.cl',    2, 2, 'Ingeniería Civil Informática');

-- ============================================================
-- 3. POSTULACIONES (10 postulaciones)
-- Tipo: 1=Nueva, 2=Existente
-- Estado: 1=En Revisión, 2=Aprobada, 3=Rechazada, 4=Cerrada
-- Regiones: 5=Coquimbo, 6=Valparaíso, 7=Metropolitana,
--           9=Maule, 11=Biobío
-- NOTA: POST-2026-010 tendrá equipo incompleto
--       → válida para Query 8 (no cumple mínimo de equipo)
-- ============================================================

INSERT INTO POSTULACION (
    numero_postulacion, codigo_interno, fecha_postulacion,
    objetivo, descripcion_soluciones, resultados_esperados,
    fecha_inicio, fecha_termino, presupuesto_total,
    id_tipo_iniciativa, id_estado, id_sede,
    id_region_ejecucion, id_region_impacto, id_empresa
) VALUES
('POST-2026-001', 'CI-2026-001', '2026-03-01',
 'Desarrollar plataforma IoT para monitoreo industrial',
 'Implementación de sensores y dashboard en tiempo real',
 'Reducción del 30% en tiempos de mantenimiento',
 '2026-04-01', '2026-12-31', 45000000.00, 1, 2, 1, 6, 7, 1),

('POST-2026-002', 'CI-2026-002', '2026-03-05',
 'Modernizar sistema de gestión de inventario',
 'Migración a plataforma cloud con BI integrado',
 'Ahorro del 25% en costos operacionales',
 '2026-04-15', '2026-12-31', 32000000.00, 2, 1, 2, 7, 6, 2),

('POST-2026-003', 'CI-2026-003', '2026-03-08',
 'Sistema de análisis de datos para manufactura',
 'Algoritmos ML para predicción de fallas en producción',
 'Reducción del 40% en paradas no planificadas',
 '2026-05-01', '2026-12-31', 55000000.00, 1, 2, 3, 5, 6, 3),

('POST-2026-004', 'CI-2026-004', '2026-03-10',
 'Automatización de procesos administrativos con IA',
 'RPA y NLP para procesamiento inteligente de documentos',
 'Ahorro de 500 horas anuales en tareas manuales',
 '2026-04-01', '2026-12-31', 28000000.00, 1, 1, 4, 6, 5, 4),

('POST-2026-005', 'CI-2026-005', '2026-03-12',
 'Plataforma e-learning adaptativa para capacitación',
 'LMS con IA para personalización de contenidos',
 'Mejora del 35% en tasas de aprendizaje',
 '2026-05-01', '2026-12-31', 38000000.00, 2, 2, 5, 11, 7, 5),

('POST-2026-006', 'CI-2026-006', '2026-03-15',
 'Sistema de trazabilidad para cadena de suministro',
 'Blockchain y códigos QR para seguimiento de productos',
 'Trazabilidad al 100% en línea de producción',
 '2026-04-01', '2026-12-31', 62000000.00, 1, 1, 1, 7, 11, 1),

('POST-2026-007', 'CI-2026-007', '2026-03-17',
 'Digitalización de procesos de calidad industrial',
 'App móvil y backend para registro de calidad',
 'Reducción del 50% en papel y tiempos de registro',
 '2026-05-15', '2026-12-31', 25000000.00, 2, 4, 2, 6, 7, 2),

('POST-2026-008', 'CI-2026-008', '2026-03-19',
 'Plataforma de gestión energética inteligente',
 'Monitoreo y optimización automática de consumo',
 'Reducción del 20% en consumo eléctrico mensual',
 '2026-04-01', '2026-12-31', 48000000.00, 1, 2, 3, 9, 7, 3),

('POST-2026-009', 'CI-2026-009', '2026-03-21',
 'Sistema de realidad aumentada para mantenimiento',
 'AR con instrucciones paso a paso para técnicos',
 'Reducción del 60% en errores de mantenimiento',
 '2026-04-01', '2026-12-31', 71000000.00, 1, 1, 4, 7, 9, 4),

('POST-2026-010', 'CI-2026-010', '2026-03-25',
 'App móvil para gestión de turnos y asistencia',
 'PWA con geolocalización y autenticación biométrica',
 'Control en tiempo real de asistencia laboral',
 '2026-05-01', '2026-12-31', 19000000.00, 2, 3, 5, 11, 6, 5);

-- ============================================================
-- 4. ETAPAS DE CRONOGRAMA
-- Restricción: máximo 36 semanas por postulación
-- EXCEPCIÓN INTENCIONAL:
--   Post 9 → 42 semanas (10+16+10+6) → EXCEDE el límite
--   → válida para validar Query 10
-- ============================================================

INSERT INTO ETAPA_CRONOGRAMA (id_postulacion, nombre_etapa, descripcion, semanas, orden) VALUES
-- Post 1: 36 semanas (8+20+8)
(1, 'Levantamiento de requerimientos', 'Análisis y documentación de necesidades del cliente', 8, 1),
(1, 'Desarrollo e implementación',     'Codificación, pruebas unitarias e integración',       20, 2),
(1, 'Despliegue y capacitación',       'Puesta en marcha y formación de usuarios finales',    8, 3),
-- Post 2: 30 semanas (6+18+6)
(2, 'Análisis de procesos actuales',   'Mapeo y documentación de flujos existentes',          6, 1),
(2, 'Desarrollo de solución cloud',    'Implementación en plataforma cloud con BI',           18, 2),
(2, 'Pruebas y entrega formal',        'QA, documentación final y entrega al cliente',        6, 3),
-- Post 3: 34 semanas (8+18+8)
(3, 'Recopilación y preparación de datos', 'ETL y limpieza de datos históricos de producción', 8, 1),
(3, 'Entrenamiento de modelos ML',         'Desarrollo, validación y ajuste de modelos',       18, 2),
(3, 'Integración y puesta en marcha',      'Despliegue en producción y monitoreo inicial',     8, 3),
-- Post 4: 28 semanas (6+16+6)
(4, 'Análisis de procesos a automatizar', 'Identificación y priorización de tareas RPA',           6, 1),
(4, 'Desarrollo de bots y módulo NLP',    'Implementación de automatizaciones y procesamiento',    16, 2),
(4, 'Validación y ajuste final',          'Pruebas con usuarios reales y correcciones finales',     6, 3),
-- Post 5: 32 semanas (6+20+6)
(5, 'Diseño instruccional y pedagógico', 'Planificación de contenidos y estrategia de aprendizaje', 6, 1),
(5, 'Desarrollo de plataforma LMS',      'Implementación técnica del sistema e-learning',           20, 2),
(5, 'Piloto y ajuste de la plataforma',  'Prueba con grupo piloto y mejoras basadas en feedback',    6, 3),
-- Post 6: 36 semanas (6+18+8+4)
(6, 'Diseño de arquitectura blockchain',    'Definición de estructura y smart contracts',        6, 1),
(6, 'Desarrollo del sistema',              'Implementación de módulos blockchain y QR',         18, 2),
(6, 'Integración con sistemas existentes', 'Conexión con ERP y plataformas actuales',            8, 3),
(6, 'Despliegue y capacitación',           'Formación de operadores y puesta en producción',     4, 4),
-- Post 7: 24 semanas (4+16+4)
(7, 'Diseño UX/UI y arquitectura',        'Wireframes, prototipos y definición técnica',        4, 1),
(7, 'Desarrollo frontend y backend',      'Implementación completa del sistema móvil',         16, 2),
(7, 'Pruebas y despliegue en producción', 'QA, correcciones y publicación oficial',             4, 3),
-- Post 8: 30 semanas (6+18+6)
(8, 'Auditoría energética inicial',           'Diagnóstico detallado del consumo actual',              6, 1),
(8, 'Implementación de sensores y dashboard', 'Instalación de dispositivos y desarrollo del sistema', 18, 2),
(8, 'Optimización y entrega final',           'Ajuste de algoritmos y documentación final',            6, 3),
-- Post 9: 42 semanas (10+16+10+6) → EXCEDE 36 semanas → Query 10
(9, 'Investigación y prototipado AR',     'Estudio de factibilidad y primer prototipo funcional',  10, 1),
(9, 'Desarrollo de módulos AR',           'Implementación de instrucciones en realidad aumentada', 16, 2),
(9, 'Integración con sistemas de planta', 'Conexión con CMMS y plataformas operativas OT',         10, 3),
(9, 'Pruebas en terreno y ajuste final',  'Validación con técnicos en campo y correcciones',        6, 4),
-- Post 10: 20 semanas (4+12+4)
(10, 'Diseño y arquitectura de la PWA',     'Definición técnica, UX y plan de desarrollo',       4, 1),
(10, 'Desarrollo de la aplicación',         'Frontend y backend con geolocalización integrada', 12, 2),
(10, 'Integración biométrica y despliegue', 'Módulo biométrico, pruebas finales y lanzamiento',  4, 3);

-- ============================================================
-- 5. INTEGRANTES DE EQUIPO
-- Profesores: id_persona 1-8 | Estudiantes: id_persona 9-20
-- Posts 1-9: 3 profesores + 5 estudiantes → CUMPLE mínimo
-- Post 10:   2 profesores + 3 estudiantes → NO CUMPLE (Query 8)
-- ============================================================

INSERT INTO INTEGRANTE_EQUIPO (id_postulacion, id_persona, rol) VALUES
-- Post 1: Prof 1,2,3 + Est 9,10,11,12,13
(1,1,'Investigador Principal'),(1,2,'Co-investigador'),(1,3,'Asesor Técnico'),
(1,9,'Alumno Tesista'),(1,10,'Alumno Tesista'),(1,11,'Alumno Tesista'),
(1,12,'Asistente de Investigación'),(1,13,'Asistente de Investigación'),
-- Post 2: Prof 1,2,4 + Est 9,10,11,12,14
(2,1,'Investigador Principal'),(2,2,'Co-investigador'),(2,4,'Asesor Técnico'),
(2,9,'Alumno Tesista'),(2,10,'Alumno Tesista'),(2,11,'Alumno Tesista'),
(2,12,'Asistente de Investigación'),(2,14,'Asistente de Investigación'),
-- Post 3: Prof 2,3,5 + Est 10,11,12,13,14
(3,2,'Investigador Principal'),(3,3,'Co-investigador'),(3,5,'Asesor Técnico'),
(3,10,'Alumno Tesista'),(3,11,'Alumno Tesista'),(3,12,'Alumno Tesista'),
(3,13,'Asistente de Investigación'),(3,14,'Asistente de Investigación'),
-- Post 4: Prof 3,4,6 + Est 11,12,13,14,15
(4,3,'Investigador Principal'),(4,4,'Co-investigador'),(4,6,'Asesor Técnico'),
(4,11,'Alumno Tesista'),(4,12,'Alumno Tesista'),(4,13,'Alumno Tesista'),
(4,14,'Asistente de Investigación'),(4,15,'Asistente de Investigación'),
-- Post 5: Prof 4,5,6 + Est 12,13,14,15,16
(5,4,'Investigador Principal'),(5,5,'Co-investigador'),(5,6,'Asesor Técnico'),
(5,12,'Alumno Tesista'),(5,13,'Alumno Tesista'),(5,14,'Alumno Tesista'),
(5,15,'Asistente de Investigación'),(5,16,'Asistente de Investigación'),
-- Post 6: Prof 5,6,7 + Est 13,14,15,16,17
(6,5,'Investigador Principal'),(6,6,'Co-investigador'),(6,7,'Asesor Técnico'),
(6,13,'Alumno Tesista'),(6,14,'Alumno Tesista'),(6,15,'Alumno Tesista'),
(6,16,'Asistente de Investigación'),(6,17,'Asistente de Investigación'),
-- Post 7: Prof 6,7,8 + Est 14,15,16,17,18
(7,6,'Investigador Principal'),(7,7,'Co-investigador'),(7,8,'Asesor Técnico'),
(7,14,'Alumno Tesista'),(7,15,'Alumno Tesista'),(7,16,'Alumno Tesista'),
(7,17,'Asistente de Investigación'),(7,18,'Asistente de Investigación'),
-- Post 8: Prof 7,8,1 + Est 15,16,17,18,19
(8,7,'Investigador Principal'),(8,8,'Co-investigador'),(8,1,'Asesor Técnico'),
(8,15,'Alumno Tesista'),(8,16,'Alumno Tesista'),(8,17,'Alumno Tesista'),
(8,18,'Asistente de Investigación'),(8,19,'Asistente de Investigación'),
-- Post 9: Prof 8,1,2 + Est 16,17,18,19,20
(9,8,'Investigador Principal'),(9,1,'Co-investigador'),(9,2,'Asesor Técnico'),
(9,16,'Alumno Tesista'),(9,17,'Alumno Tesista'),(9,18,'Alumno Tesista'),
(9,19,'Asistente de Investigación'),(9,20,'Asistente de Investigación'),
-- Post 10: Prof 3,4 + Est 9,10,11 → NO CUMPLE MÍNIMO (Query 8)
(10,3,'Investigador Principal'),(10,4,'Co-investigador'),
(10,9,'Alumno Tesista'),(10,10,'Alumno Tesista'),(10,11,'Asistente de Investigación');

-- ============================================================
-- EVIDENCIA MÍNIMA REQUERIDA (Parte 4)
-- ============================================================

-- (1) COUNT de tablas principales
SELECT 'POSTULACION'      AS tabla, COUNT(*) AS filas FROM POSTULACION   UNION ALL
SELECT 'EMPRESA',                   COUNT(*)           FROM EMPRESA       UNION ALL
SELECT 'PERSONA',                   COUNT(*)           FROM PERSONA       UNION ALL
SELECT 'INTEGRANTE_EQUIPO',         COUNT(*)           FROM INTEGRANTE_EQUIPO UNION ALL
SELECT 'ETAPA_CRONOGRAMA',          COUNT(*)           FROM ETAPA_CRONOGRAMA  UNION ALL
SELECT 'REGION',                    COUNT(*)           FROM REGION        UNION ALL
SELECT 'SEDE',                      COUNT(*)           FROM SEDE;

-- (2) Postulación completa con JOIN (postulación 1)
SELECT
    p.numero_postulacion,
    p.fecha_postulacion,
    ti.descripcion          AS tipo_iniciativa,
    ep.descripcion          AS estado,
    s.nombre_sede           AS sede,
    re.nombre_region        AS region_ejecucion,
    ri.nombre_region        AS region_impacto,
    e.nombre_empresa        AS empresa,
    p.presupuesto_total
FROM POSTULACION p
JOIN TIPO_INICIATIVA    ti ON p.id_tipo_iniciativa   = ti.id_tipo_iniciativa
JOIN ESTADO_POSTULACION ep ON p.id_estado            = ep.id_estado
JOIN SEDE               s  ON p.id_sede              = s.id_sede
JOIN REGION             re ON p.id_region_ejecucion  = re.id_region
JOIN REGION             ri ON p.id_region_impacto    = ri.id_region
JOIN EMPRESA            e  ON p.id_empresa           = e.id_empresa
WHERE p.id_postulacion = 1;

-- (3) Verificación regla 3 profesores + 5 estudiantes
SELECT
    p.numero_postulacion,
    SUM(CASE WHEN tp.descripcion = 'Profesor'   THEN 1 ELSE 0 END) AS profesores,
    SUM(CASE WHEN tp.descripcion = 'Estudiante' THEN 1 ELSE 0 END) AS estudiantes,
    CASE
        WHEN SUM(CASE WHEN tp.descripcion = 'Profesor'   THEN 1 ELSE 0 END) >= 3
         AND SUM(CASE WHEN tp.descripcion = 'Estudiante' THEN 1 ELSE 0 END) >= 5
        THEN 'CUMPLE'
        ELSE 'NO CUMPLE'
    END AS cumple_minimo
FROM POSTULACION p
JOIN INTEGRANTE_EQUIPO ie ON p.id_postulacion   = ie.id_postulacion
JOIN PERSONA           pe ON ie.id_persona      = pe.id_persona
JOIN TIPO_PERSONA      tp ON pe.id_tipo_persona = tp.id_tipo_persona
GROUP BY p.id_postulacion, p.numero_postulacion
ORDER BY p.id_postulacion;
