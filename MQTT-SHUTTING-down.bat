@echo off
REM Script to close ALL CMD windows gracefully, then force close if needed
set LOG_FILE=C:\path\to\your\logfile.log

echo %date% %time% - Starting to close all CMD windows... >> %LOG_FILE%

REM First, try to gracefully close all CMD windows (except this one)
echo %date% %time% - Attempting graceful closure of all CMD windows... >> %LOG_FILE%

REM Send WM_CLOSE message to all CMD windows except current one
powershell -Command "Get-Process cmd | Where-Object {$_.Id -ne $PID -and $_.Id -ne (Get-WmiObject Win32_Process -Filter \"ProcessId=$PID\").ParentProcessId} | ForEach-Object {$_.CloseMainWindow()}"

REM Wait for graceful closure
timeout /t 5 /nobreak > nul

REM Check what's still running and force close if needed
echo %date% %time% - Checking for remaining processes... >> %LOG_FILE%

REM Force close specific MQTT processes
taskkill /F /IM mosquitto.exe 2>nul
taskkill /F /IM php.exe /FI "COMMANDLINE eq *artisan*mqtt*" 2>nul

REM Force close any remaining CMD windows (except current one)
for /f "skip=1 tokens=2" %%i in ('tasklist /FI "IMAGENAME eq cmd.exe" /FO CSV') do (
    if not "%%~i"=="%~dp0" (
        echo %date% %time% - Force closing CMD PID: %%~i >> %LOG_FILE%
        taskkill /F /PID %%~i 2>nul
    )
)

echo %date% %time% - All CMD windows closed. >> %LOG_FILE%
echo All CMD windows have been closed.

exit