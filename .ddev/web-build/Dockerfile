ARG BASE_IMAGE
FROM $BASE_IMAGE

RUN wget https://packages.sury.org/php/apt.gpg -O /etc/apt/trusted.gpg.d/php-sury.gpg
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install \
    -y -o Dpkg::Options::="--force-confold" --no-install-recommends --no-install-suggests mc graphicsmagick
RUN a2enmod macro
