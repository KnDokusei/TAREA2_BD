# CT-USM — Sistema de Postulación de Iniciativas
## Tarea 2 — INF-239 Bases de Datos

**Integrantes:**
- Matías Pérez — ROL: 202121025-3
- René Aspee   — ROL: 202110011-3

---

## Instrucciones de instalación y ejecución en XAMPP

### 1. Requisitos
- XAMPP con Apache y MySQL activos
- PHP
- MySQL
- Navegador (Chrome, Firefox, Edge)

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

> Pegar el contenido en la pestaña **SQL** y presionar "Continuar".

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

### 6. Objetos SQL implementados

| Objeto               | Nombre                       | Dónde se usa                             |
|----------------------|------------------------------|------------------------------------------|
| **VIEW**             | `VW_POSTULACIONES_COMPLETAS` | Listado, búsqueda, inicio, detalle       |
| **FUNCTION**         | `fn_cumple_equipo_minimo()`  | Mis postulaciones, ver postulación       |
| **STORED PROCEDURE** | `sp_enviar_postulacion()`    | Botón "Enviar" en postulaciones          |
| **TRIGGER**          | `trg_log_cambio_estado`      | Registra automáticamente cambios estado  |

---

### 7. Supuestos adoptados

1. El responsable académico (ROL 1) puede ver en el listado general todas las postulaciones que no estén en estado Borrador, independientemente de quién las creó. Esto permite tener contexto del proceso sin exponer borradores ajenos.
2. La contraseña de todos los usuarios de prueba es `password` (hash BCrypt compatible con PHP `password_verify`).
3. Al "Asignar evaluador" desde el panel Admin, se cambia el estado de Enviada → En Revisión automáticamente.
4. El equipo mínimo requerido es 3 profesores + 5 estudiantes, validado tanto en la FUNCTION SQL como en el Stored Procedure al enviar.
5. El cronograma máximo es 36 semanas, validado en el Stored Procedure.
6. La tabla `DOCUMENTO` existe en el DDL original pero no se implementó el módulo de carga de archivos, ya que el enunciado no lo especifica como funcionalidad web obligatoria.
7. La búsqueda avanzada por "evaluador asignado" filtra postulaciones que tienen una evaluación registrada o asignada a ese evaluador, independientemente de si ya ingresó una nota.

---

### 8. Bonus implementados

- **Bootstrap 5.3** — Diseño completo, responsive, con sidebar, topbar y badges de estado.
- **Protección SQL Injection** — Todos los queries usan `PDO` con prepared statements (`$pdo->prepare(...)->execute([...])`). Ninguna variable se interpola directamente en SQL.
