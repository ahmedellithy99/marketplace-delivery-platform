#!/bin/bash
set -euo pipefail

PROJECT_DIR="/home/ahmedell/maywaay.com"
LOCK_FILE="${PROJECT_DIR}/storage/framework/queue-worker.lock"
LOG_FILE="${PROJECT_DIR}/storage/logs/queue-worker.log"

# Common paths for PHP on shared hosting
PHP_BIN=""
for path in /usr/local/bin/php /usr/bin/php /opt/alt/php83/usr/bin/php /opt/alt/php82/usr/bin/php /opt/alt/php81/usr/bin/php; do
    if [ -x "${path}" ]; then
        PHP_BIN="${path}"
        break
    fi
done

if [ -z "${PHP_BIN}" ]; then
    PHP_BIN=$(command -v php || true)
fi

if [ -z "${PHP_BIN}" ]; then
    echo "$(date): ERROR: PHP binary not found" >> "${LOG_FILE}"
    exit 1
fi

# Prevent overlapping runs using a lock file
if [ -f "${LOCK_FILE}" ]; then
    PID=$(cat "${LOCK_FILE}" 2>/dev/null || true)
    if [ -n "${PID}" ] && kill -0 "${PID}" 2>/dev/null; then
        echo "$(date): Queue worker already running (PID ${PID}). Skipping." >> "${LOG_FILE}"
        exit 0
    fi
fi

cd "${PROJECT_DIR}" || exit 1

# Write current PID to lock file
echo $$ > "${LOCK_FILE}"

# Remove lock file on exit
trap 'rm -f "${LOCK_FILE}"' EXIT

echo "$(date): Starting queue worker with ${PHP_BIN}..." >> "${LOG_FILE}"

# Process notification jobs on default and whatsapp queues
"${PHP_BIN}" artisan queue:work \
    --queue=default,whatsapp \
    --tries=3 \
    --stop-when-empty \
    --timeout=0 \
    >> "${LOG_FILE}" 2>&1

echo "$(date): Queue worker finished." >> "${LOG_FILE}"
