#!/bin/bash

# Function to display usage
usage_list_services() {
    echo "Usage: $0 --format {json|csv|table|list} [services...]"
    echo "Example: $0 --format table nginx php8.3-fpm mysql"
    exit 1
}

# Log Function
log_message_services() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >>/var/log/spikster_cli.log
}

list_services() {
    # List of all possible services and processes
    ALL_SERVICES=("nginx" "php8.3-fpm" "mysql" "redis-server" "exim4" "dovecot" "spamassassin" "clamav-daemon")
    ALL_PROCESSES=("glances")
    ALL_PACKAGES=("roundcube")

    # Function to check service status
    check_service_status() {
        service=$1
        if systemctl list-unit-files | grep -q "^$service.service"; then
            if systemctl is-active --quiet $service; then
                echo "ACTIVE"
            else
                echo "INACTIVE"
            fi
        elif pgrep -x "$service" >/dev/null; then
            echo "ACTIVE"
        elif dpkg -l | grep -qw "$service"; then
            echo "INACTIVE"
        else
            echo "NOT INSTALLED"
        fi
    }

    # Parse arguments
    if [[ $# -lt 2 ]]; then
        usage
    fi

    if [[ $1 != "--format" ]]; then
        usage
    fi

    OUTPUT_FORMAT=$2
    shift 2

    SERVICES=("${@:-${ALL_SERVICES[@]}}")
    PROCESSES=("${@:-${ALL_PROCESSES[@]}}")
    PACKAGES=("${@:-${ALL_PACKAGES[@]}}")

    # Check and store service statuses
    declare -A SERVICE_STATUSES PROCESS_STATUSES PACKAGE_STATUSES
    # Check systemd services and processes
    for service in "${SERVICES[@]}"; do
        SERVICE_STATUSES[$service]=$(check_service_status $service)
    done

    for process in "${PROCESSES[@]}"; do
        PROCESS_STATUSES[$process]=$(check_service_status $process)
    done

    # Check packages
    for package in "${PACKAGES[@]}"; do
        if dpkg -l | grep -qw "$package"; then
            PACKAGE_STATUSES[$package]="INSTALLED"
        else
            PACKAGE_STATUSES[$package]="NOT INSTALLED"
        fi
    done

    # Generate output in the specified format
    generate_output() {
        case $OUTPUT_FORMAT in
        json)
            echo "{"
            echo "  \"services\": ["
            SERVICE_SEPARATOR=""
            for service in "${!SERVICE_STATUSES[@]}"; do
                echo -n "$SERVICE_SEPARATOR    { \"name\": \"$service\", \"status\": \"${SERVICE_STATUSES[$service]}\" }"
                SERVICE_SEPARATOR=",\n"
            done
            echo -e "\n  ],"
            echo "  \"processes\": ["
            PROCESS_SEPARATOR=""
            for process in "${!PROCESS_STATUSES[@]}"; do
                echo -n "$PROCESS_SEPARATOR    { \"name\": \"$process\", \"status\": \"${PROCESS_STATUSES[$process]}\" }"
                PROCESS_SEPARATOR=",\n"
            done
            echo -e "\n  ],"
            echo "  \"packages\": ["
            PACKAGE_SEPARATOR=""
            for package in "${!PACKAGE_STATUSES[@]}"; do
                echo -n "$PACKAGE_SEPARATOR    { \"name\": \"$package\", \"status\": \"${PACKAGE_STATUSES[$package]}\" }"
                PACKAGE_SEPARATOR=",\n"
            done
            echo -e "\n  ]"
            echo "}"
            ;;
        csv)
            echo "Type,Service,Status"
            for service in "${!SERVICE_STATUSES[@]}"; do
                echo "Service,$service,${SERVICE_STATUSES[$service]}"
            done
            for process in "${!PROCESS_STATUSES[@]}"; do
                echo "Process,$process,${PROCESS_STATUSES[$process]}"
            done
            for package in "${!PACKAGE_STATUSES[@]}"; do
                echo "Package,$package,${PACKAGE_STATUSES[$package]}"
            done
            ;;
        table)
            printf "%-10s %-20s %-15s\n" "Type" "Service" "Status"
            printf "%-10s %-20s %-15s\n" "----" "-------" "------"
            for service in "${!SERVICE_STATUSES[@]}"; do
                printf "%-10s %-20s %-15s\n" "Service" "$service" "${SERVICE_STATUSES[$service]}"
            done
            for process in "${!PROCESS_STATUSES[@]}"; do
                printf "%-10s %-20s %-15s\n" "Process" "$process" "${PROCESS_STATUSES[$process]}"
            done
            for package in "${!PACKAGE_STATUSES[@]}"; do
                printf "%-10s %-20s %-15s\n" "Package" "$package" "${PACKAGE_STATUSES[$package]}"
            done
            ;;
        list)
            for service in "${!SERVICE_STATUSES[@]}"; do
                echo "Service: $service: ${SERVICE_STATUSES[$service]}"
            done
            for process in "${!PROCESS_STATUSES[@]}"; do
                echo "Process: $process: ${PROCESS_STATUSES[$process]}"
            done
            for package in "${!PACKAGE_STATUSES[@]}"; do
                echo "Package: $package: ${PACKAGE_STATUSES[$package]}"
            done
            ;;
        *)
            usage
            ;;
        esac
    }

    # Run the output generation
    generate_output

    log_message "Service, process, and package list displayed in $OUTPUT_FORMAT format."
}
