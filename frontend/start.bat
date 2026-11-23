@echo off
set "PATH=C:\Users\Andrew\.config\herd\bin\php85;C:\Users\Andrew\.config\herd\bin;%PATH%"
cd /d "%~dp0"
echo ========================================================
echo   Recruitment Platform Frontend (TalentBridge)
echo   API Backend: http://hrapi.itsolution.solutions/api
echo   Frontend URL: http://127.0.0.1:8080
echo ========================================================
echo.
timeout /t 2 /nobreak >nul
start http://127.0.0.1:8080
php -S 127.0.0.1:8080 -t public server.php
pause
