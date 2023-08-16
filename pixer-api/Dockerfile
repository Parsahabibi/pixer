FROM laravelsail/php81-composer

RUN apt-get update -y && apt-get install -y libpng-dev zlib1g-dev libicu-dev g++

RUN docker-php-ext-configure intl 

RUN docker-php-ext-install exif gd intl zip

