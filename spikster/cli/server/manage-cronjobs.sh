#!/bin/bash

# Function to display usage
usage() {
    echo "Usage: $0 {get|update} [cron_file]"
    echo "Example to get current cronjobs: $0 get"
    echo "Example to update cronjobs: $0 update /path/to/cron_file"
    exit 1
}

# Log Function
log_message() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >>/var/log/spikster_manage_cron.log
}

# Function to get current cronjobs
get_cronjobs() {
    crontab -l
    log_message "Fetched current cronjobs."
}

manage_cronjobs() {
    # Function to update cronjobs
    update_cronjobs() {
        local cron_file="$1"
        if [ ! -f "$cron_file" ]; then
            echo "Error: Cron file '$cron_file' not found."
            log_message "Error: Cron file '$cron_file' not found."
            exit 1
        fi

        crontab "$cron_file"
        if [ $? -eq 0 ]; then
            echo "Cronjobs updated successfully from file '$cron_file'."
            log_message "Cronjobs updated successfully from file '$cron_file'."
        else
            echo "Error: Failed to update cronjobs from file '$cron_file'."
            log_message "Error: Failed to update cronjobs from file '$cron_file'."
            exit 1
        fi
    }

    # Parse arguments
    if [ $# -lt 1 ]; then
        usage
    fi

    ACTION=$1
    CRON_FILE=$2

    case $ACTION in
    get)
        get_cronjobs
        ;;
    update)
        if [ -z "$CRON_FILE" ]; then
            usage
        fi
        update_cronjobs "$CRON_FILE"
        ;;
    *)
        usage
        ;;
    esac
}
