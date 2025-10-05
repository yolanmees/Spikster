#!/bin/bash

# Spikster Agent Build Script
# This script builds the Go agent for Linux amd64 architecture

set -e

echo "🔨 Building Spikster Agent..."

# Set build variables
VERSION="1.0.0"
BUILD_DIR="build"
BINARY_NAME="spikster-agent"

# Create build directory
mkdir -p ${BUILD_DIR}

# Build for Linux AMD64 (most common for servers)
echo "📦 Building for Linux AMD64..."
GOOS=linux GOARCH=amd64 go build \
    -ldflags="-s -w -X main.Version=${VERSION}" \
    -o ${BUILD_DIR}/${BINARY_NAME}-linux-amd64 \
    main.go

# Build for Linux ARM64 (for ARM servers like AWS Graviton)
echo "📦 Building for Linux ARM64..."
GOOS=linux GOARCH=arm64 go build \
    -ldflags="-s -w -X main.Version=${VERSION}" \
    -o ${BUILD_DIR}/${BINARY_NAME}-linux-arm64 \
    main.go

# Show file sizes
echo ""
echo "✅ Build complete!"
echo ""
ls -lh ${BUILD_DIR}/

# Make binaries executable
chmod +x ${BUILD_DIR}/${BINARY_NAME}-*

echo ""
echo "📋 Next steps:"
echo "  1. Test: ./${BUILD_DIR}/${BINARY_NAME}-linux-amd64"
echo "  2. Deploy: scp ${BUILD_DIR}/${BINARY_NAME}-linux-amd64 user@server:/usr/local/bin/spikster-agent"
echo "  3. Install service: systemctl enable spikster-agent"
