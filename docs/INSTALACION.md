# 📦 Guía de instalación detallada

## 1. Requisitos del entorno

- **Windows** con el **XAMPP portable** ubicado en
  `C:\Users\LENOVO\Desktop\xammp`
  - PHP CLI: `C:\Users\LENOVO\Desktop\xammp\php\php.exe` (PHP 8.2+)
  - MySQL/MariaDB: `C:\Users\LENOVO\Desktop\xammp\mysql\bin\mysqld.exe`
- Navegador: Chrome / Edge / Firefox actualizado.

> EduAlert JJP **no requiere Composer ni ninguna instalación adicional**:
> las librerías (Bootstrap, Chart.js, DataTables, TCPDF, etc.) están
> incluidas en `public/assets/vendor/` y `storage/vendor/`.

## 2. Iniciar el servidor MySQL

Abra una terminal (PowerShell o CMD) y ejecute:

```powershell
C:\Users\LENOVO\Desktop\xammp\mysql\bin\mysqld.exe --console
```

Déjelo corriendo en esa ventana. Si lo detiene, el sistema mostrará
"Sin conexión a la base de datos" hasta que lo reinicie.

> **Alternativa:** usar el Panel de Control de XAMPP normalmente
> (Iniciar → Apache y MySQL).

## 3. Ubicar la aplicación

Opción A — **Servidor de desarrollo** (recomendado durante el desarrollo/tests):

```powershell
cd "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP"
& "C:\Users\LENOVO\Desktop\xammp\php\php.exe" -S localhost:8000 -t public public/server.php
```

> ⚠️ Debe ejecutarse **desde la carpeta `EduAlertJJP`** (para que `-t public` y
> `public/server.php` apunten a los archivos correctos).

Opción B — **Apache de XAMPP**:

Copie la carpeta `EduAlertJJP` dentro de `C:\Users\LENOVO\Desktop\xammp\htdocs`
y acceda vía `http://localhost/edualert`.

## 4. Asistente de instalación (1 sola vez)

1. Abra `http://localhost:8000/instalacion` (o `http://localhost/edualert/instalacion`).
2. **Paso 1 — Crear base de datos:** presione el botón. Se creará la base
   `edualert_jjp`, el esquema (25 tablas) y los datos base (roles, permisos,
   usuarios demo, grados, secciones, cursos, periodos y parámetros).
3. **Paso 2 — Datos demo:** presione el botón para generar **200 estudiantes**
   peruanos con matrícula en todos los periodos, calificaciones, asistencia,
   asignación de docentes y apoderados.
4. Al terminar aparecerá el mensaje de éxito. El sistema quedará instalado
   (se crea el archivo `storage/.instalado`; el acceso a `/instalacion` queda
   bloqueado).

## 5. Ingreso al sistema

URL de acceso: `http://localhost:8000` (o `http://localhost/edualert`)

En la pantalla de login puede:
- escribir la cuenta manualmente según rol, o
- presionar los **botones de acceso rápido** que llenan las credenciales demo.

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@edualert.test` | `12345678` |
| Director | `director@edualert.test` | `12345678` |
| Docente | `docente@edualert.test` | `12345678` |
| Tutor | `tutor@edualert.test` | `12345678` |
| Auxiliar | `auxiliar@edualert.test` | `12345678` |

## 6. Primeros pasos recomendados

1. Ingrese como **admin**.
2. Vaya a **Alertas Tempranas → Ejecutar motor de alertas**: evaluará a los
   200 estudiantes del periodo y creará las alertas según los puntajes.
3. Revise el **Dashboard**: los gráficos se llenarán con datos reales.
4. Abra una alerta **_Alto_** → **Registrar seguimiento** (intervención).
5. Genere un **Reporte** y expórtelo en **PDF**, **Excel** o **CSV**.

## 7. Puesta en producción (para la sustentación)

- Mover a `htdocs\edualert` como en la Opción B.
- La `BASE_URL` se autodetecta; no requiere configuración extra.
- Revisar permisos de escritura de `storage/` (logs y `.instalado`).

## 8. Versión portátil (para otras máquinas)

Se entrega **`EduAlertJJP-Portable`** (carpeta autocontenida, también como
**`EduAlertJJP-Portable.zip`**). Incluye la aplicación, **PHP 8.2** y **MySQL**
en `xampp\` y la **base demo ya instalada** (no hay que pasar por el asistente).

**Uso en cualquier PC con Windows 64 bits:**

1. Copie/descomprima la carpeta donde quiera (da igual la ruta).
2. Doble clic en **`iniciar.bat`** (o `iniciar.ps1`), que:
   - genera `xampp\mysql\my.ini` con las rutas de esa ubicación (por eso
     funciona movida de máquina),
   - arranca MySQL (puerto 3306) y el servidor web (puerto 8000),
   - abre el navegador en `http://127.0.0.1:8000`.
3. Ingrese con las cuentas demo (sección 5).
4. Para apagar: doble clic en **`detener.bat`** (solo detiene los procesos de
   esa carpeta).

Notas:
- Los datos se guardan dentro de la carpeta (`xampp\mysql\data`); para
  respaldar o clonar, copie esa carpeta completa.
- Si por algún motivo la base se pierde, abra `/instalacion` y ejecute los
  2 pasos del asistente para recrearla.
- Puertos 3306/8000 ocupados por otro programa: ciérrelo antes o el lanzador
  lo indicará.
- Para **regenerar** el paquete desde el proyecto (tras cambios), ejecute
  `storage\empacar.ps1` en la máquina de origen (requiere detener MySQL
  previamente para copiar el datadir de forma consistente) y luego comprima
  la carpeta resultante con `tar -a -c -f ...