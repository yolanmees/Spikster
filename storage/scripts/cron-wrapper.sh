#!/bin/bash

# Spikster Cron Job Execution Wrapper
# This script wraps cron job execution and logs to database

CRON_JOB_ID=$1
COMMAND="${@:2}"

# API endpoint (adjust to your panel URL)
PANEL_URL="${PANEL_URL:-http://localhost}"
API_TOKEN="${SPIKSTER_API_TOKEN}"

START_TIME=$(date +%s)
START_DATETIME=$(date -u +"%Y-%m-%d %H:%M:%S")

# Create execution record
EXECUTION_ID=$(curl -s -X POST "${PANEL_URL}/api/cron-executions" \
    -H "Authorization: Bearer ${API_TOKEN}" \
    -H "Content-Type: application/json" \
    -d "{\"cron_job_id\": ${CRON_JOB_ID}, \"started_at\": \"${START_DATETIME}\"}" \
    | jq -r '.id')

# Execute the command and capture output
OUTPUT_FILE=$(mktemp)
ERROR_FILE=$(mktemp)

eval "$COMMAND" > "$OUTPUT_FILE" 2> "$ERROR_FILE"
EXIT_CODE=$?

END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))
END_DATETIME=$(date -u +"%Y-%m-%d %H:%M:%S")

OUTPUT=$(cat "$OUTPUT_FILE" | head -c 10000)  # Limit to 10KB
ERROR_OUTPUT=$(cat "$ERROR_FILE" | head -c 10000)

# Determine status
if [ $EXIT_CODE -eq 0 ]; then
    STATUS="success"
else
    STATUS="failed"
fi

# Update execution record
curl -s -X PATCH "${PANEL_URL}/api/cron-executions/${EXECUTION_ID}" \
    -H "Authorization: Bearer ${API_TOKEN}" \
    -H "Content-Type: application/json" \
    -d "{
        \"finished_at\": \"${END_DATETIME}\",
        \"duration\": ${DURATION},
        \"status\": \"${STATUS}\",
        \"exit_code\": ${EXIT_CODE},
        \"output\": $(echo "$OUTPUT" | jq -Rs .),
        \"error_output\": $(echo "$ERROR_OUTPUT" | jq -Rs .)
    }" > /dev/null

# Cleanup
rm -f "$OUTPUT_FILE" "$ERROR_FILE"

exit $EXIT_CODE
