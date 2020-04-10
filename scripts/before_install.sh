# Remove all files and folders in the deployment folder
rm -R /var/www/html/* -f
rm -R /var/www/html/{,.[!.],..?}* -f
