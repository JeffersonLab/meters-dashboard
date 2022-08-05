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



