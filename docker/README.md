# Docker configuration

To aid development and local testing, the PublicWhip repository comes with a Docker configuration ready to use - just run `docker-compose up` from this directory.

The following services will be available:
* Port 80 and 443: The Website. HTTPs on port 443 is not yet configured.
* Port 8025 : Mailhog
* Port 8080 : PHP My Admin

Internally, it has the following images:
* publicwhip-nginx : Nginx web server running 1.15.9 on Alpine
* publicwhip-php   : PHP running 7.3-fpm on Alpine
* publicwhip-mariadb : MariaDB database server 10.3 on Bionic
* publicwhip-phpmyadmin: PHP My Admin (for database access)
* publicwhip-mailhog: Mailhog for mail interception

The alpine images can be access via ssh using:

`docker exec -it publicwhip-nginx /bin/ash`

whilst the others can be accessed using

`docker exec -it publicwhip-mariadb /bin/bash`

You should therefore be able to get a test environment up and running just by running `docker-compose up` from this directory.

If you make any changes to the configuration files, you'll need to force a rebuild by running `docker-compose up --build`.