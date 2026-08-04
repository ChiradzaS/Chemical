@echo off
REM Path to the Laravel project directory (replace with the correct path)
cd C:\xampp\htdocs\LaravelCRUD

REM Ensure you're in the correct directory by checking for artisan file
if not exist artisan (
    echo "Artisan file not found. Please check the path."
    pause
    exit /b
)

REM Run the Laravel Artisan backup command
echo Running database backup onlocal...
php artisan backup:run --only-db
echo Running database backup oncloud...
php artisan my:custom-command

echo Database backup has been completed.
pause