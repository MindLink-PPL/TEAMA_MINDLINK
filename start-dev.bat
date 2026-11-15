@echo off
setlocal
set PATH=D:\app\php-8.4.27-1-nts-Win32-vs17-x64;C:\laragon\bin\git\bin;%PATH%
cd /d "%~dp0"
echo ============================================================
echo Starting MindLink Local Server at http://127.0.0.1:8000
echo ============================================================
php -S 127.0.0.1:8000 -t public
