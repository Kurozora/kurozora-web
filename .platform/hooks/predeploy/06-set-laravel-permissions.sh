#!/bin/bash

# Set nginx as owner of the application
chown -R nginx:nginx .

# All files to 644
sudo find . -type f -exec chmod 644 {} \;

# All directories to 755
sudo find . -type d -exec chmod 755 {} \;
