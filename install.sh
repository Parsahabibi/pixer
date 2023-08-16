#!/bin/sh
cd pixer-api

FILE=.env
if [ ! -f "$FILE" ]; then
    echo 'Creating .env file...'
    cp .env.example .env
fi

echo "Building docker image..."
docker build -t="redq/php81-composer" .

echo "Installing composer dependencies..."
docker run --rm \
    -v $(pwd):/opt \
    -w /opt \
    redq/php81-composer:latest \
    composer install

echo "Stopping running sail container if any..."
./vendor/bin/sail down

echo "Starting app container..."
./vendor/bin/sail up -d

echo "Generating application key..."
./vendor/bin/sail artisan key:generate

sleep 30 & PID=$! #simulate a long process

echo "Processing please be patient..."
printf "["
# While process is running...
while kill -0 $PID 2> /dev/null; do
    printf  "▇"
    sleep 1
done
echo  "]"

echo "Installing marvel dependncies..."
./vendor/bin/sail artisan marvel:install

FILE=public/storage
if [ ! -h "$FILE" ]; then
    echo "Linking storage path..."
    ./vendor/bin/sail artisan storage:link
fi

echo "Installation successfull!"

echo "- REST Endpoint: http://localhost/"