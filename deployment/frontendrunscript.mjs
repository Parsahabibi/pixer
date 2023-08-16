#!/usr/bin/env zx

echo(chalk.blue('#Step 1 - Installing Frontend project dependencies'))

echo('Please wait a while till the successful installation of the dependencies')

echo(chalk.blue("Install shop packages"));
await $`yarn --cwd /var/www/pixer-laravel/shop/`;

echo(chalk.blue("Running for shop with pm2"));
await $`pm2 --name shop-rest start yarn --cwd /var/www/pixer-laravel/shop -- run start`;

echo(chalk.blue("Install admin packages"));
await $`yarn --cwd /var/www/pixer-laravel/admin/`;

echo(chalk.blue("Running for admin with pm2"));
await $`pm2 --name shop-rest start yarn --cwd /var/www/pixer-laravel/admin -- run start`;
