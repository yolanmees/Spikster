#!/bin/bash

# Function to display usage
usage_cron() {
    echo "Usage: $0 {get|update} [cron_file]"
    echo "Example to get current cronjobs: $0 get"
    echo "Example to update cronjobs: $0 update /path/to/cron_file"
    exit 1
}

# Log Function
log_message_cron() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >>/var/log/spikster_manage_cron.log
}

# Function to get current cronjobs
get_cronjobs() {
    crontab -l
    log_message_cron "Fetched current cronjobs."
}

# Function to update cronjobs
update_cronjobs() {
    local cron_file="$1"
    if [ ! -f "$cron_file" ]; then
        echo "Error: Cron file '$cron_file' not found."
        log_message_cron "Error: Cron file '$cron_file' not found."
        exit 1
    fi

    crontab "$cron_file"
    if [ $? -eq 0 ]; then
        echo "Cronjobs updated successfully from file '$cron_file'."
        log_message_cron "Cronjobs updated successfully from file '$cron_file'."
    else
        echo "Error: Failed to update cronjobs from file '$cron_file'."
        log_message_cron "Error: Failed to update cronjobs from file '$cron_file'."
        exit 1
    fi
}

# Main function to handle cronjob management
manage_cronjobs() {
    # Parse arguments
    if [ $# -lt 1 ]; then
        usage_cron
    fi

    ACTION=$1
    CRON_FILE=$2

    case $ACTION in
    get)
        get_cronjobs
        ;;
    update)
        if [ -z "$CRON_FILE" ]; then
            usage_cron
        fi
        update_cronjobs "$CRON_FILE"
        ;;
    *)
        usage_cron
        ;;
    esac
}
