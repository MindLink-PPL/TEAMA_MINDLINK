@echo off
setlocal
set PATH=D:\app\php-8.4.27-1-nts-Win32-vs17-x64;C:\laragon\bin\git\bin;%PATH%
cd /d "%~dp0"
echo ============================================================
echo Running Laravel Dusk Browser Tests for MindLink
echo ============================================================
php artisan dusk %*
pause
