#!/bin/bash

# Initialization file.
# The task: We need to run php artisan migrate --seed only once for the initial database initialization
# The solution: Create an init script that holds the initial commands and execute them when a marker is not present


# Check if the application has been initialized
if [ ! -e /var/www/html/initialized ]; then
    # Run your initialization commands
    php /var/www/html/artisan key:generate
    php /var/www/html/artisan migrate --seed --force
    php /var/www/html/artisan storage:link

    # Create a marker file to indicate that initialization has been completed
    touch /var/www/html/initialized
fi

# Continue with the default entrypoint (e.g., starting Apache or PHP-FPM)
exec "$@"
