#!/usr/bin/env zx

echo(chalk.blue('Front-end project build'))

echo(chalk.blue('#Step 9: Setting Up Server & Project'))
let domainName = await question('What is your domain name? ')
echo(chalk.green(`Your domain name is: ${domainName} \n`))

echo(chalk.blue('#Step 1 - Config Next Admin App For /admin Sub Directory'))
await $`cp admin/next.config.js ./admin/temp.js`
await $`awk '{sub(/i18n,/, "i18n,basePath:\`/admin\`,"); print $0}' ./admin/temp.js > ./admin/next.config.js`
await $`rm -rf ./admin/temp.js`

echo(chalk.blue('#Step 1 - Installing Frontend project dependencies'))

echo('Please wait a while till the successful installation of the dependencies')
echo('yarn')

await $`rm -f ./shop/.env`
await $`cp ./shop/.env.template ./shop/.env`
await $`chmod -R 777 ./shop/.env`
await $`awk '{gsub(/NEXT_PUBLIC_REST_API_ENDPOINT=http:\\/\\/localhost/,"NEXT_PUBLIC_REST_API_ENDPOINT=https://${domainName}/backend"); print $0}' ./shop/.env.template > ./shop/.env`

await $`rm -f ./admin/.env`
await $`cp ./admin/.env.template ./admin/.env`
await $`chmod -R 777 ./admin/.env`
await $`awk '{gsub(/NEXT_PUBLIC_REST_API_ENDPOINT="http:\\/\\/localhost"/,"NEXT_PUBLIC_REST_API_ENDPOINT=\\"https://${domainName}/backend\\""); print $0}' ./admin/.env.template > ./admin/.env`;

await $`cp ./shop/next.config.js ./shop/temp.js`
await $`awk '{sub(/domains:\\ \\[/, "domains: [ \`${domainName}\`,"); print $0}' ./shop/temp.js > ./shop/next.config.js`

await $`rm -rf ./shop/temp.js`

await $`cp ./admin/next.config.js ./admin/temp.js`
await $`awk '{sub(/domains:\\ \\[/, "domains: [ \`${domainName}\`,"); print $0}' ./admin/temp.js > ./admin/next.config.js`
await $`rm -rf ./admin/temp.js`


echo('Install Node For Frontend')
await $`yarn --cwd ./admin`
await $`yarn --cwd ./shop`

echo('Build Frontend')
await $`yarn --cwd ./shop build`
await $`yarn --cwd ./admin build`

echo(chalk.blue('#Upload project file to server'))
let username = await question('Enter your server username (ex: ubuntu): ')
let ip_address = await question('Enter server ip address (ex: 11.111.111.11): ')

echo("########### connecting to server... ###########")

echo("Remove node_modules folder")
await $`rm -rf shop/node_modules`
await $`rm -rf admin/node_modules`

echo("Zipping shop, admin folder")
await $`zip -r frontend.zip shop admin`

echo(chalk.green('frontend.zip file created'))
// let front_end_source_path = await question('Enter frontend.zip source path (ex: /home/../pixer-laravel/frontend.zip): ')
let front_end_source_path = "./frontend.zip";
echo("Uploading frontend.zip to server, Please wait...")
await $`scp ${front_end_source_path} ${username}@${ip_address}:/var/www/pixer-laravel`
echo(chalk.green("Uploaded frontend.zip to server"))

await $`ssh -o StrictHostKeyChecking=no -l ${username} ${ip_address} "unzip /var/www/pixer-laravel/frontend.zip -d /var/www/pixer-laravel";`

echo(chalk.green('Your application build and upload successful'))
