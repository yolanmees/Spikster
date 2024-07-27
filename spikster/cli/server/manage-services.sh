#!/bin/bash

# Function to display usage
usage() {
    echo "Usage: $0 --format {json|csv|table|list} {start|stop|restart} [services...]"
    echo "Example: $0 --format table start nginx php8.3-fpm mysql"
    exit 1
}

# Log Function
log_message() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >>/var/log/spikster_manage.log
}

manage_services() {
    # List of all possible services, processes, and packages
    SYSTEMD_SERVICES=("nginx" "apache2" "mysql" "php8.3-fpm")
    PROCESSES=("glances")
    PACKAGES=("roundcube")

    # Function to perform actions on services
    perform_action() {
        action=$1
        service=$2

        if [[ " ${SYSTEMD_SERVICES[@]} " =~ " ${service} " ]]; then
            case $action in
            start)
                if systemctl start $service; then
                    return 0
                else
                    return 1
                fi
                ;;
            stop)
                if systemctl stop $service; then
                    return 0
                else
                    return 1
                fi
                ;;
            restart)
                if systemctl restart $service; then
                    return 0
                else
                    return 1
                fi
                ;;
            *)
                usage
                ;;
            esac
        elif [[ " ${PROCESSES[@]} " =~ " ${service} " ]]; then
            case $action in
            start)
                nohup $service >/dev/null 2>&1 &
                if [[ $? -eq 0 ]]; then
                    return 0
                else
                    return 1
                fi
                ;;
            stop)
                pkill -f $service
                if [[ $? -eq 0 ]]; then
                    return 0
                else
                    return 1
                fi
                ;;
            restart)
                pkill -f $service
                nohup $service >/dev/null 2>&1 &
                if [[ $? -eq 0 ]]; then
                    return 0
                else
                    return 1
                fi
                ;;
            *)
                usage
                ;;
            esac
        elif [[ " ${PACKAGES[@]} " =~ " ${service} " ]]; then
            echo "$service:PACKAGE-NO-ACTION"
            log_message "$service is a package and cannot be started, stopped, or restarted"
            return 2
        else
            echo "$service:INVALID"
            log_message "$service is not a valid service, process, or package"
            return 3
        fi
    }

    # Function to check service action status
    check_service_status_after_action() {
        action=$1
        service=$2

        if [[ " ${SYSTEMD_SERVICES[@]} " =~ " ${service} " ]] || [[ " ${PROCESSES[@]} " =~ " ${service} " ]]; then
            if systemctl is-active --quiet $service || pgrep -x "$service" >/dev/null; then
                echo "${service}:ACTIVE_AFTER_${action^^}"
            else
                echo "${service}:INACTIVE_AFTER_${action^^}"
            fi
        elif [[ " ${PACKAGES[@]} " =~ " ${service} " ]]; then
            if dpkg -l | grep -qw "$service"; then
                echo "$service:INSTALLED"
            else
                echo "$service:NOT_INSTALLED"
            fi
        else
            echo "$service:INVALID"
        fi
    }

    # Parse arguments
    if [[ $# -lt 3 ]]; then
        usage
    fi

    if [[ $1 != "--format" ]]; then
        usage
    fi

    OUTPUT_FORMAT=$2
    ACTION=$3
    shift 3

    SERVICES=("${@}")

    # Perform action and check status for each service
    declare -A SERVICE_STATUSES

    for service in "${SERVICES[@]}"; do
        perform_action $ACTION $service
        SERVICE_STATUSES[$service]=$(check_service_status_after_action $ACTION $service)
    done

    # Generate output in the specified format
    generate_output() {
        case $OUTPUT_FORMAT in
        json)
            echo "{"
            echo "  \"services\": ["
            FIRST_ITEM=true
            for service in "${!SERVICE_STATUSES[@]}"; do
                if $FIRST_ITEM; then
                    FIRST_ITEM=false
                else
                    echo "    ,"
                fi
                echo "    { \"name\": \"$service\", \"status\": \"${SERVICE_STATUSES[$service]#*:}\" }"
            done
            echo "  ]"
            echo "}"
            ;;
        csv)
            echo "Service,Status"
            for service in "${!SERVICE_STATUSES[@]}"; do
                echo "$service,${SERVICE_STATUSES[$service]#*:}"
            done
            ;;
        table)
            printf "%-20s %-25s\n" "Service" "Status"
            printf "%-20s %-25s\n" "-------" "------"
            for service in "${!SERVICE_STATUSES[@]}"; do
                printf "%-20s %-25s\n" "$service" "${SERVICE_STATUSES[$service]#*:}"
            done
            ;;
        list)
            for service in "${!SERVICE_STATUSES[@]}"; do
                echo "$service: ${SERVICE_STATUSES[$service]#*:}"
            done
            ;;
        *)
            usage
            ;;
        esac
    }

    # Run the output generation
    generate_output

    log_message "Service action '$ACTION' performed and status displayed in $OUTPUT_FORMAT format."
}
