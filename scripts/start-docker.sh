#!/bin/bash

# Check if Docker is running and start it if not
if ! docker info > /dev/null 2>&1; then
    echo 'Starting Docker...'
    open -a Docker
    
    # Wait for Docker to be fully started
    while ! docker info > /dev/null 2>&1; do
        echo 'Waiting for Docker to start...'
        sleep 2
    done
    
    echo 'Docker is now running!'
else
    echo 'Docker is already running.'
fi