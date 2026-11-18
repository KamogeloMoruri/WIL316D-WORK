#!/usr/bin/env bash
# Starts the AI Donation Tracker.
# Uses Docker if it's available (no PHP install needed at all).
# Falls back to a local PHP install if Docker isn't found.

set -e
cd "$(dirname "$0")"

if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
    echo "Docker found - starting the app in a container..."
    docker compose up --build
elif command -v php >/dev/null 2>&1; then
    echo "PHP found locally - starting PHP's built-in server..."
    echo "Open http://localhost:8000/website.html in your browser."
    php -S 0.0.0.0:8000
else
    echo "Neither Docker nor PHP was found on this system."
    echo "Install Docker Desktop (recommended, no PHP setup needed): https://www.docker.com/products/docker-desktop/"
    echo "or install PHP directly: https://www.php.net/manual/en/install.php"
    exit 1
fi
