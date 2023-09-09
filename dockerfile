# Use the official Ubuntu image as base
FROM ubuntu:22.04

# Install wget
RUN apt-get update && apt-get install -y wget

# Download and run the script
RUN wget -O - https://raw.githubusercontent.com/yolanmees/Spikster/master/go.sh | bash

ENV PORT=8080

# Expose ports
EXPOSE 80 443
