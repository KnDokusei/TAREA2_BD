# CT-USM — Sistema de Postulación de Iniciativas
## Tarea 2 — INF-239 Bases de Datos

**Integrantes:**
- Matías Pérez — ROL: 202121025-3
- René Aspee   — ROL: 202110011-3

---

## Instrucciones de instalación y ejecución en XAMPP

### 1. Requisitos
- XAMPP con Apache y MySQL activos
- PHP 8.1 o superior
- MySQL 8.0 o superior
- Navegador web moderno (Chrome, Firefox, Edge)

---

### 2. Configurar la base de datos (orden estricto)

**Desde XAMPP:**
1. Abre el **XAMPP Control Panel**
2. Presiona **Start** en **Apache**
3. Presiona **Start** en **MySQL**
4. Presiona **Admin** en la fila de **MySQL** → se abre `http://localhost/phpmyadmin`

**Importar los scripts SQL en este orden exacto:**

En phpMyAdmin, selecciona la base de datos (o créala primero):
- Haz clic en **"Nueva"** en el panel izquierdo
- Nombre: `ct_usm_postulaciones` → clic en **Crear**

Luego ve a la pestaña **SQL** y ejecuta cada archivo en orden:

| Orden | Archivo                        | Descripción                                      |
|-------|--------------------------------|--------------------------------------------------|
| 1     | `ddl_ct_usm.sql`               | Crea todas las tablas base                       |
| 2     | `dml_ct_usm.sql`               | Inserta datos de prueba                          |
| 3     | `ddl_extension_t2.sql`         | Agrega ROL, USUARIO, LOG, EVALUACION             |
| 4     | `objetos_t2.sql`               | VIEW, FUNCTION, PROCEDURE, TRIGGER               |

> Para importar: pestaña **Importar** → Seleccionar archivo → Ejecutar.
> O pega el contenido en la pestaña **SQL** y presiona "Continuar".

#### ⚠️ Si ya tienes la BD instalada y aparecen evaluaciones duplicadas

Ejecuta **una sola vez** el siguiente script de limpieza:

| Archivo                          | Cuándo usar                                                                 |
|----------------------------------|-----------------------------------------------------------------------------|
| `fix_duplicados_evaluacion.sql`  | Solo si la BD ya estaba instalada y presenta duplicados en EVALUACION       |

Este script:
1. Elimina filas duplicadas en `EVALUACION`, conservando solo la más reciente por postulación + usuario
2. Agrega el constraint `UNIQUE (id_postulacion, id_usuario)` si aún no existe
3. Muestra una consulta de verificación al final (si no devuelve filas, la limpieza fue exitosa)

> **No ejecutar** en una instalación limpia desde cero — `ddl_extension_t2.sql` ya incluye el constraint.

---

### 3. Instalar la aplicación PHP

1. Copia la carpeta `ct_usm` completa a:
   ```
   C:\xampp\htdocs\ct_usm\
   ```
2. Verifica que la ruta quede así:
   ```
   C:\xampp\htdocs\ct_usm\index.php
   C:\xampp\htdocs\ct_usm\app.php
   C:\xampp\htdocs\ct_usm\config\db.php
   ...
   ```

---

### 4. Acceder a la aplicación

**Abre tu navegador y entra a:**
```
http://localhost/ct_usm/
```

> **Nota:** El botón **Admin** de MySQL en XAMPP abre phpMyAdmin (gestión de BD).
> Para ver la **aplicación web**, debes escribir la URL anterior directamente en el navegador.

---

### 5. Usuarios de prueba

Todos tienen contraseña: **`password`**

| Usuario          | Rol                    | Acceso                                          |
|------------------|------------------------|-------------------------------------------------|
| `carlos.ramirez` | Postulante (ROL 1)     | Crear/editar/enviar postulaciones               |
| `ana.gonzalez`   | Postulante (ROL 1)     | Crear/editar/enviar postulaciones               |
| `rodrigo.vega`   | Coordinador (ROL 2)    | Revisar y evaluar postulaciones                 |
| `patricia.leal`  | Coordinador (ROL 2)    | Revisar y evaluar postulaciones                 |
| `admin`          | Administrador (ROL 3)  | Gestionar evaluadores y asignaciones            |

---

### 6. Estructura del proyecto

```
ct_usm/
├── index.php              ← Login
├── app.php                ← Router principal
├── config/
│   └── db.php             ← Conexión PDO + helpers
├── includes/
│   ├── sidebar.php        ← Navegación lateral
│   └── badge_estado.php   ← Helper badge de estados
├── pages/
│   ├── inicio.php         ← Dashboard (todos los roles)
│   ├── listado.php        ← Listado general + búsqueda
│   ├── mis_postulaciones.php  ← ROL 1
│   ├── nueva_postulacion.php  ← ROL 1
│   ├── editar_postulacion.php ← ROL 1
│   ├── ver_postulacion.php    ← Todos los roles
│   ├── busqueda.php           ← Filtros avanzados
│   ├── evaluaciones.php       ← ROL 2 y 3
│   └── admin.php              ← ROL 3
├── actions/
│   ├── auth.php           ← Login / Logout
│   ├── postulacion.php    ← CRUD postulaciones + SP
│   ├── equipo.php         ← CRUD integrantes
│   ├── cronograma.php     ← CRUD etapas
│   ├── evaluacion.php     ← Registrar evaluación
│   └── admin.php          ← Acciones ROL 3
├── css/
│   └── style.css
├── js/
│   └── app.js
└── sql/
    └── objetos_t2.sql     ← VIEW, FUNCTION, SP, TRIGGER
```

---

### 7. Objetos SQL implementados

| Objeto               | Nombre                       | Dónde se usa                             |
|----------------------|------------------------------|------------------------------------------|
| **VIEW**             | `VW_POSTULACIONES_COMPLETAS` | Listado, búsqueda, inicio, detalle       |
| **FUNCTION**         | `fn_cumple_equipo_minimo()`  | Mis postulaciones, ver postulación       |
| **STORED PROCEDURE** | `sp_enviar_postulacion()`    | Botón "Enviar" en postulaciones          |
| **TRIGGER**          | `trg_log_cambio_estado`      | Registra automáticamente cambios estado  |

---

### 8. Supuestos adoptados

1. El responsable académico (ROL 1) puede ver el listado general de postulaciones no-borrador de otros usuarios, para tener contexto del proceso.
2. La contraseña de todos los usuarios de prueba es `password` (hash BCrypt compatible con PHP `password_verify`).
3. Al "Asignar evaluador" desde el panel Admin, se cambia el estado de Enviada → En Revisión automáticamente.
4. El equipo mínimo requerido es 3 profesores + 5 estudiantes, validado tanto en la FUNCTION SQL como en el Stored Procedure al enviar.
5. El cronograma máximo es 36 semanas, validado en el Stored Procedure.
6. La tabla `DOCUMENTO` existe en el DDL original pero no se implementó el módulo de carga de archivos, ya que el enunciado no lo especifica como funcionalidad web obligatoria.
7. La búsqueda avanzada por "evaluador asignado" filtra por postulaciones que tienen al menos una evaluación registrada por ese usuario.
8. La navegación usa `app.php?page=...` con router interno, sin recarga completa del layout.

---

### 9. Bonus implementados

- **Bootstrap 5.3** — Diseño completo, responsive, con sidebar, topbar y badges de estado.
- **Protección SQL Injection** — Todos los queries usan `PDO` con prepared statements (`$pdo->prepare(...)->execute([...])`). Ninguna variable se interpola directamente en SQL.
