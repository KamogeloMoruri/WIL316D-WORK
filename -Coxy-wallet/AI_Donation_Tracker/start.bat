@echo off
REM Starts the AI Donation Tracker.
REM Uses Docker if it's available (no PHP install needed at all).
REM Falls back to a local PHP install if Docker isn't found.

cd /d "%~dp0"

where docker >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    docker info >nul 2>nul
    if %ERRORLEVEL% EQU 0 (
        echo Docker found - starting the app in a container...
        docker compose up --build
        goto :eof
    )
)

where php >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo PHP found locally - starting PHP's built-in server...
    echo Open http://localhost:8000/website.html in your browser.
    php -S 0.0.0.0:8000
    goto :eof
)

echo Neither Docker nor PHP was found on this system.
echo Install Docker Desktop (recommended, no PHP setup needed): https://www.docker.com/products/docker-desktop/
echo or install PHP directly: https://www.php.net/downloads
pause
