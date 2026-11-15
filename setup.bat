@echo off
chcp 65001 >nul
set ROOT=%~dp0

echo === Backend ===
cd /d "%ROOT%weather_backend" || exit /b 1
call composer install || goto :fail

set FIRST_RUN=0
if not exist .env (
    copy .env.example .env >nul
    call php artisan key:generate || goto :fail
    set FIRST_RUN=1
)

echo.
echo Make sure MySQL is running and DB_* in weather_backend\.env are correct.
echo If the database does not exist, artisan will offer to create it.
call php artisan migrate || goto :fail
if "%FIRST_RUN%"=="1" call php artisan db:seed || goto :fail

echo === Frontend ===
cd /d "%ROOT%weather_frontend" || exit /b 1
call npm install || goto :fail

echo.
echo Done. Run start.bat to launch the project.
pause
exit /b 0

:fail
echo.
echo Setup failed.
pause
exit /b 1
