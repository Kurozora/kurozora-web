#!/bin/bash

# Enable the workers
systemctl enable laravel_worker@{1..3}.service

# Restart the workers
systemctl restart laravel_worker@{1..3}.service
