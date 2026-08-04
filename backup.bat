@echo off
REM Set the path to the PHP executable
set PHP_PATH="C:\path\to\php.exe"


REM Set the path to the PHP script
set SCRIPT_PATH="C:\path\to\upload_backup.php"

REM Run the PHP script
%PHP_PATH% %SCRIPT_PATH%

REM Pause to view output (optional)
pause
