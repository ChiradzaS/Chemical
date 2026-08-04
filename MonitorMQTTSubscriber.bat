@echo off
REM Define paths
set LARAVEL_PATH=C:\xampp\htdocs\LaravelCRUD
set LOG_FILE=C:\path\to\your\logfile.log
set MOSQUITTO_PATH="C:\Program Files\mosquitto\mosquitto.exe"

REM Change to Laravel directory
cd %LARAVEL_PATH%

REM Check for Artisan file
if not exist artisan (
    echo %date% %time% - ERROR: Artisan file not found at %LARAVEL_PATH%. >> %LOG_FILE%
    echo ERROR: Artisan file not found at %LARAVEL_PATH%.
    pause
    exit /b 1
)

echo %date% %time% - Starting service cleanup and restart... >> %LOG_FILE%
echo Starting service cleanup and restart...

REM Kill existing Mosquitto processes
echo %date% %time% - Stopping existing Mosquitto processes... >> %LOG_FILE%
taskkill /F /IM mosquitto.exe /T >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo %date% %time% - Existing Mosquitto processes terminated. >> %LOG_FILE%
) else (
    echo %date% %time% - No existing Mosquitto processes found. >> %LOG_FILE%
)

REM Kill existing MQTT subscriber processes (more specific approach)
echo %date% %time% - Stopping existing MQTT subscriber processes... >> %LOG_FILE%
for /f "tokens=2" %%i in ('tasklist /FI "IMAGENAME eq php.exe" /FO CSV ^| findstr /i "artisan"') do (
    taskkill /F /PID %%i >nul 2>&1
)

REM Kill any cmd windows with MQTT-related titles
taskkill /F /FI "WINDOWTITLE eq Mosquitto Broker*" >nul 2>&1
taskkill /F /FI "WINDOWTITLE eq MQTT Subscriber*" >nul 2>&1

REM Wait for cleanup to complete
ping 127.0.0.1 -n 3 > nul

echo %date% %time% - Starting Mosquitto broker... >> %LOG_FILE%
echo Starting Mosquitto broker...

REM Create a temporary Mosquitto config file for local access
echo listener 1883 > %TEMP%\mosquitto_local.conf
echo allow_anonymous true >> %TEMP%\mosquitto_local.conf

REM Start the Mosquitto broker in a minimized window with local config
start /min "Mosquitto Broker" cmd /k %MOSQUITTO_PATH% -c %TEMP%\mosquitto_local.conf -v

REM Give it a few seconds to start
ping 127.0.0.1 -n 5 > nul

REM Verify Mosquitto is running
tasklist /FI "IMAGENAME eq mosquitto.exe" > nul
if %ERRORLEVEL% EQU 0 (
    echo %date% %time% - Mosquitto broker started successfully. >> %LOG_FILE%
    echo Mosquitto broker started successfully.
) else (
    echo %date% %time% - ERROR: Mosquitto broker failed to start. >> %LOG_FILE%
    echo ERROR: Mosquitto broker failed to start.
    pause
    exit /b 1
)

echo %date% %time% - Starting MQTT subscriber... >> %LOG_FILE%
echo Starting MQTT subscriber...

REM Start MQTT subscriber in a minimized window with proper error handling
start /min "MQTT Subscriber" cmd /k "cd /d %LARAVEL_PATH% && php artisan mqtt:subscribe"

REM Wait a bit for the subscriber to start
ping 127.0.0.1 -n 5 > nul

REM Verify MQTT subscriber is running by checking for php processes
tasklist /FI "IMAGENAME eq php.exe" > nul
if %ERRORLEVEL% EQU 0 (
    echo %date% %time% - MQTT subscriber started successfully. >> %LOG_FILE%
    echo MQTT subscriber started successfully.
) else (
    echo %date% %time% - WARNING: Could not verify MQTT subscriber status. >> %LOG_FILE%
    echo WARNING: Could not verify MQTT subscriber status.
)

echo %date% %time% - Service startup completed. >> %LOG_FILE%
echo Service startup completed.
echo.
echo Services are now running in minimized windows.
echo To stop services, run this script again.
echo.  
exit 