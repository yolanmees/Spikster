#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"
echo "Building WordPress daemon handler (linux/amd64)..."
GOOS=linux GOARCH=amd64 go build -o handler main.go
echo "Done: handler (linux/amd64)"
