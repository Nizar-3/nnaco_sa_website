@echo off
title nnaco_sa Groq AI Chat Server
cd /d "%~dp0groq-chat-server"
echo ====================================================
echo  Menjalankan nnaco_sa Groq AI Chat Server (Port 3001)
echo ====================================================
echo.
node server.js
pause
