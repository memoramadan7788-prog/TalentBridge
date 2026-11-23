@echo off
if not exist "%USERPROFILE%\.talentbridge_license" (
    echo =========================================================
    echo [ERROR] Unauthorized Execution Environment Detected.
    echo Missing proprietary developer license token.
    echo =========================================================
    pause
    exit /b 1
)

title TalentBridge System Launcher
cd /d "%~dp0"
echo ========================================================
echo       Starting TalentBridge Recruitment Platform
echo ========================================================
echo.

echo [Checking MySQL Database...]
netstat -ano | findstr 3306 >nul
if %errorlevel% neq 0 (
    echo [MySQL] Starting MySQL Database from XAMPP...
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start "MySQL Database" /min "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
        timeout /t 2 /nobreak >nul
    ) else (
        echo [Warning] MySQL not found at C:\xampp\mysql. Please ensure MySQL is running manually.
    )
) else (
    echo [MySQL] Database is already running on port 3306.
)
echo.

echo [1/3] Starting Backend API (Port 8001)...
start "TalentBridge Backend API" cmd /c "cd /d "%~dp0backend" && php -S 127.0.0.1:8001 -t public"

echo [2/3] Starting Frontend Web Platform (Port 8080)...
start "TalentBridge Frontend Platform" cmd /c "cd /d "%~dp0frontend" && php -S 127.0.0.1:8080 -t public server.php"

echo [3/3] Starting AI ATS Engine (Port 8000)...
start "TalentBridge AI ATS Service" cmd /c "cd /d "%~dp0ai_ats_service" && python -m uvicorn main:app --host 127.0.0.1 --port 8000"

echo.
echo ========================================================
echo All services started successfully! Opening browser...
echo ========================================================
