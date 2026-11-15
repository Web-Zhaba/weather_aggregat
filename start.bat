@echo off
chcp 65001 >nul
set ROOT=%~dp0

start "Backend (Laravel)" cmd /k "cd /d "%ROOT%weather_backend" && php artisan serve"
start "Frontend (Vite)" cmd /k "cd /d "%ROOT%weather_frontend" && npm run dev -- --host"
