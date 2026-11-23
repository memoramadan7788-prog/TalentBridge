@echo off
title AI ATS Evaluation Server
cd /d "%~dp0"
echo ========================================================
echo        AI ATS Recruitment Engine (Ollama Local)
echo ========================================================
echo Server running at: http://127.0.0.1:8500
echo Web UI available at: http://127.0.0.1:8500
echo.
python main.py
pause
