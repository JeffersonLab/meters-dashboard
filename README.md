# meters-dashboard
Web-based dashboard for viewing and reporting on Power, Water, and Gas meters


## Quick Start

The steps below can be used to bring this application up quickly in a docker container.

```
git clone https://github.com/JeffersonLab/meters-dashboard.git

cd meters-dashboard

docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php80-composer:latest \
    composer install --ignore-platform-reqs

docker compose up -d --build

```
After which the application will be available at http://localhost/


## Hints

### Run npm and install packages

```shell
sail shell
$ export NODE_EXTRA_CA_CERTS=/usr/local/share/ca-certificates/JLabCA.crt
$ npm install bootstrap-vue # or whatever

```
### Interact with mySQL container
```shell
# Obtain a shell
docker exec -it meters-db bash

# Clean out existing database
drop database laravel;
create database laravel;
grant all privileges on laravel.* to 'sail'@'%';

# Import a file into mysql
cd /var/lib/mysql  
mysql -u root -p laravel < 2022-08-10_13.meters.bak
```


### Interact with the softIOC 
It is configured by default to mimic bldg 87 data.

```shell
% docker exec meters-softioc caget 87-L2:commErr
87-L2:commErr                  0
% docker exec meters-softioc caget 87-L2:totkW
87-L2:totkW                    13
% docker exec meters-softioc caget 87-L2:llVolt
87-L2:llVolt                   480
% docker exec meters-softioc caput 87-L2:llVolt 501
Old : 87-L2:llVolt                   480
New : 87-L2:llVolt                   501
% docker exec meters-softioc caget 87-L2:llVolt.STAT
87-L2:llVolt.STAT              HIHI

# To restart the softioc after editing run/softioc/db/softioc.db
docker restart meters-softioc

```
also see https://hub.docker.com/r/slominskir/softioc


### Interact with the epicsweb container

http://localhost:8080/epics2web/

```php
# Note that we use the hostname defined in docker-compose.yml rather than localhost
# when we access epicsweb from our laravel application container.
var_dump(file_get_contents('http://epics2web:8080/epics2web/caget?pv=87-L1%3AllVolt'));

```
also see https://hub.docker.com/r/slominskir/epics2web

