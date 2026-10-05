# 🎓 EduAlert JJP — Sistema de Alertas Tempranas Académicas

**Sistema web para la detección temprana de riesgo académico** en la
**Institución Educativa Juan Jiménez Pimentel — Tarapoto** (San Martín, Perú).

EduAlert JJP integra la información académica (matrículas, calificaciones y
asistencia) y, mediante un **motor de alertas tempranas con umbrales
configurables**, clasifica a los estudiantes por niveles de riesgo, genera
alertas automáticas, permite el seguimiento de intervenciones y produce
reportes exportables.

> 🛠️ **Stack:** PHP 8.2+ nativo (MVC propio, sin Composer) · MySQL/MariaDB ·
> Bootstrap 5.3 · Chart.js · DataTables · SweetAlert2 · TCPDF — **todo local/offline**.

---

## ✨ Módulos

| Módulo | Descripción |
|---|---|
| **Autenticación** | Login con 5 roles, recuperación de contraseña, sesión segura (bcrypt + CSRF) |
| **Dashboard** | KPI en tiempo real y 5 gráficos con datos reales del sistema |
| **Estudiantes** | Registro integral (datos, apoderado, matrícula) con ficha académica y evolución |
| **Gestión académica** | Periodos, grados, secciones, cursos, asignación docente↔curso↔sección |
| **Matrículas** | Control de inscripciones por periodo, grado y sección |
| **Calificaciones** | Registro masivo de notas por sección y curso; consulta filtrada |
| **Asistencia** | Registro diario (presente/tardanza/falta/justificado) y resumen por sección |
| **Motor de alertas** | Calcula puntaje de riesgo (rendimiento + asistencia + desaprobados + tendencia) y genera alertas |
| **Seguimiento** | Intervenciones por alerta (entrevistas, reforzamiento, derivaciones...) |
| **Reportes** | 8 reportes exportables a **PDF, Excel (XLS) y CSV** |
| **Notificaciones** | Avisos internos por rol con lectura/leídas |
| **Usuarios y roles** | ADMINISTRADOR, DIRECTOR, DOCENTE, TUTOR, AUXILIAR con permisos por módulo |
| **Auditoría** | Registro de todas las acciones del sistema |
| **Configuración** | Umbrales y puntajes del motor editables (sin tocar código) |

---

## 🚀 Instalación rápida

### Requisitos
- **XAMPP portable** (PHP 8.2+ y MySQL/MariaDB), ya disponible en
  `C:\Users\LENOVO\Desktop\xammp`
- Navegador moderno (Chrome, Edge)

### Pasos
1. Inicie **MySQL** del XAMPP:
   `C:\Users\LENOVO\Desktop\xammp\mysql\bin\mysqld.exe --console`
2. Copie la carpeta `EduAlertJJP` a `htdocs` del XAMPP (o use el servidor de
   desarrollo).
3. Abra el navegador en `http://localhost/edualert` (o `http://localhost:8000`
   si usa `php -S`).
4. El **asistente de instalación** de 2 pasos creará la base de datos y
   cargará **~200 estudiantes de demo** con datos reales.
5. Inicie sesión con una de las cuentas demo (abajo) y ejecute el
   **Motor de alertas** desde el módulo de Alertas.

### Cuentas demo (contraseña: `12345678`)

| Rol | Correo |
|---|---|
| Administrador | `admin@edualert.test` |
| Director | `director@edualert.test` |
| Docente | `docente@edualert.test` |
| Tutor | `tutor@edualert.test` |
| Auxiliar | `auxiliar@edualert.test` |

---

## 📦 Versión portátil — abre el sistema en cualquier PC (sin instalar nada)

Junto al proyecto se entrega **`EduAlertJJP-Portable`** (carpeta) y
**`EduAlertJJP-Portable.zip`** (~130 MB): aplicación + **PHP 8.2** + **MySQL** +
base de datos demo, todo autocontenido.

1. Copie o descomprima la carpeta en la PC destino (puede quedar en cualquier
   ruta, incluso con espacios).
2. Haga doble clic en **`iniciar.bat`** → inicia MySQL y el servidor web y abre
   el navegador en `http://127.0.0.1:8000`.
3. Inicie sesión con las cuentas demo (tabla de arriba).
4. Para apagar: doble clic en **`detener.bat`**.

> No instala nada en el sistema operativo; los datos viven **dentro** de la
> carpeta (`xampp\mysql\data`). El lanzador genera la configuración según la
> ruta donde esté la carpeta, por lo que funciona en cualquier equipo Windows
> 64 bits. Si los puertos 3306/8000 están ocupados, el lanzador lo indica.

📖 **Documentación completa:**
- [Guía de instalación detallada](docs/INSTALACION.md)
- [Cuentas y datos de demostración](docs/DEMO.md)
- [Solución de problemas](docs/SOLUCION_DE_PROBLEMAS.md)
- [Guion de sustentación de tesis](docs/GUION_SUSTENTACION.md)

---

## 🗂️ Estructura del proyecto

```
EduAlertJJP/
├── app/
│   ├── Core/            # Router, Controller, Model, Database, Auth, Validator
│   ├── Controllers/     # Lógica de negocio por módulo
│   ├── Models/          # Acceso a datos (MySQL / PDO)
│   ├── Services/        # RiskEngine, Auditor, Notificador, Exportador, SeederDemo
│   └── helpers.php      # Funciones auxiliares (vistas, roles, formatos)
├── config/              # Configuración global
├── database/            # schema.sql + seed_base.sql
├── public/              # Front controller, assets (JS/CSS/vendor)
├── resources/views/     # Vistas (layouts + módulos)
├── routes/web.php       # Rutas con permisos
└── storage/             # Logs, .instalado, vendor TCPDF
```

## 🔐 Seguridad implementada
- Contraseñas con `password_hash()` (bcrypt)
- Tokens **CSRF** en todos los formularios
- Consultas parametrizadas (**prepared statements**) contra inyección SQL
- Escape de salida (`e()`) contra **XSS**
- Permisos por rol verificados en cada ruta (backend) y en el menú (frontend)
- Auditoría de acciones sensibles y bloqueo de login por intentos fallidos

## 🧪 Cadena funcional demostrable
> Estudiante → Matrícula → Cursos → Calificaciones/Asistencia → Motor de riesgo
> → Alerta → Dashboard → Seguimiento → Reportes (PDF/Excel/CSV)

Con los datos demo cargados puede reproducir este recorrido completo en
menos de 5 minutos (ver [Guion de sustentación](docs/GUION_SUSTENTACION.md)).