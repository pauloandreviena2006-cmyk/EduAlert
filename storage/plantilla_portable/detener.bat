@echo off
rem EduAlert JJP - detener servicios (doble clic aqui)
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0detener.ps1"
echo.
pause