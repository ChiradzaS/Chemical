@echo off
REM Path to XAMPP installation directory
cd C:\xampp

REM Start Apache
echo Starting Apache...
call xampp_start.exe

REM Start MySQL
echo Starting Mysql...
call mysql_start.bat

echo XAMPP services have started  



