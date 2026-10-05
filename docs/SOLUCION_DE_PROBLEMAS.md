# 🛠️ Solución de problemas

## 1. "Sin conexión a la base de datos" / página de error en blanco

**Causa:** el servidor MySQL no está corriendo.

**Solución:**
```powershell
C:\Users\LENOVO\Desktop\xammp\mysql\bin\mysqld.exe --console
```
Verifique además que el puerto 3306 esté libre:
```powershell
netstat -ano | findstr :3306
```

## 2. `php -S` muestra el directorio o no encuentra el servidor

- Ejecute el comando **desde la carpeta `EduAlertJJP`**:
  ```powershell
  cd "C:\Users\LENOVO\Desktop\proyec tesis\EduAlertJJP"
  & "C:\Users\LENOVO\Desktop\xammp\php\php.exe" -S localhost:8000 -t public public/server.php
  ```
- Si el puerto 8000 está ocupado, use otro: `-S localhost:8001`.

## 3. "Vista no encontrada" o errores 500 al navegar

- Consulte el log: `storage/logs/app.log` (muestra el stack trace si
  `APP_DEBUG=true` en `config/config.php`).
- Verifique que la carpeta `resources/views/` mantenga la estructura original
  (todas las vistas existen; no borre subcarpetas).

## 4. El login no responde / "No puede ingresar"

- Confirme que la instalación terminó (existe `storage/.instalado` y la tabla
  `users` tiene filas).
- Si olvidó la contraseña: restáurela desde el enlace **"¿Olvidó su
  contraseña?"** del login (funciona con el correo demo y la configuración de
  correo del servidor), o bien restablezca con SQL:
  ```sql
  UPDATE users SET password = '$2y$10$51NM42tR4Y4w3uMw4c/wxuznEBotZADAkRhWSEIjYd/6QIyGBSVli' WHERE email = 'admin@edualert.test';
  ```
  (esa es la versión hash de `12345678`).
- Reinicie el servidor PHP tras cambiar el archivo `config/config.php`.

## 5. El motor de alertas no genera alertas

- Ejecute el motor con usuarios con **calificaciones** y **asistencia**
  registrados (el SeederDemo ya los crea).
- Verifique en **Configuración** los umbrales (nota aprobatoria 11, niveles de
  riesgo 14/11, puntajes 20/40, etc.).
- El motor solo evalúa estudiantes con al menos una calificación en el periodo
  activo. Los que no estén en riesgo no generan alerta (correcto).

## 6. Los gráficos del dashboard no cargan

- Los datos vienen de `dashboard/datos` (JSON). Ábralo en el navegador
  mientras está logueado y verifique que responda JSON.
- Si usa Apache y aparece error 404, revise que `public/.htaccess` esté
  presente y que `mod_rewrite` esté habilitado.

## 7. Errores con acentos o emojis (�)

- Confirme que las tablas usan `utf8mb4` (el `schema.sql` ya lo define).
- Verifique que `config/config.php` fija `utf8mb4` en el charset de la
  conexión PDO.

## 8. Exportación PDF en blanco o sin abrir

- El HTTPS/espacio `storage/vendor/tcpdf` debe conservar todos los archivos
  (fuentes incluidas).
- Si TCPDF da error de "font", borre la caché en `storage/vendor/tcpdf/cache/`
  (si existe) y reintente.

## 9. "El asistente de instalación está bloqueado"

- Significa que `storage/.instalado` existe. Para reinstalar: detenga MySQL,
  elimine la base `edualert_jjp` y el archivo `storage/.instalado`, y vuelva a
  `/instalacion`.

## 10. Otros errores

- Navegue con la URL del sistema tras el dominio (la `BASE_URL` se
  autodetecta). Si el proyecto vive en una subcarpeta distinta, verifique que
  la carpeta `public/` sea la raíz web (o el `-t public` en el servidor de
  desarrollo).
- Todos los errores controlados quedan en `storage/logs/app.log` con fecha y
  contexto; consúltelo antes de reportar.