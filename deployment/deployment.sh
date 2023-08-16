#! /bin/bash

echo "Enter your server username (ex: ubuntu)"
read username
echo "Enter server ip address (ex: 11.111.111.11)"
read ip_address
echo "########### connecting to server... ###########"
echo "${username}"
echo "${ip_address}"
ssh -o StrictHostKeyChecking=no -l "${username}" "${ip_address}" "sudo mkdir -p /var/www/pixer-laravel;sudo chown -R \$USER:\$USER /var/www; sudo apt install zip unzip";

if [ -d "./pixer-api" ]; then
  echo 'Zipping pixer-api folder'
  zip -r ./pixer-api.zip ./pixer-api
fi

if [ -d "./deployment" ]; then
  echo 'Zipping deployment folder'
  zip -r ./deployment.zip ./deployment
fi

if [ -f "./pixer-api.zip" ] && [ -f "./deployment.zip" ]; then
    # echo "Enter your pixer-api.zip file path"
    # read api_source_path
    echo "Uploading pixer-api.zip to server"
    scp "./pixer-api.zip" "${username}@${ip_address}:/var/www/pixer-laravel"
    echo "uploaded pixer-api.zip to server"
    ssh -o StrictHostKeyChecking=no -l "${username}" "${ip_address}" "unzip /var/www/pixer-laravel/pixer-api.zip -d /var/www/pixer-laravel";

    # echo "Enter your deployment.zip file path"
    # read deployment_source_path
    echo 'Uploading deployment.zip to server...'
    scp "./deployment.zip" "${username}@${ip_address}:/var/www/pixer-laravel"
    echo 'uploaded deployment.zip to server'
    ssh -o StrictHostKeyChecking=no -l "${username}" "${ip_address}" "unzip /var/www/pixer-laravel/deployment.zip -d /var/www/pixer-laravel";
else
  echo "pixer-api and deployment zip file missing"
fi

echo "installing google zx for further script"
npm i -g zx

echo "Congrats, All the deployment script and api files uploaded to the server."
