# Calendar Track (WiP)

This is a barebones PHP application for use as a display in front of a conference rooms (we opted to use iPads.).

## Deployment (WiP)

Docker Compose is the recommended way to deploy the application.  
I will eventually host a proper [dockerhub](https://hub.docker.com/) image, but Docker Compose is easier (and more familiar) to maintain for now.

1. Utilize the [Docker Compose file](docker-compose.yml)
2. Copy `./.env` to `./.env` and edit as necessary
3. Copy `./includes/config.example.php` to `./includes/config.php` and edit as necessary.

### Weather

Use `https://api.weather.gov/points/LAT,LONG` to find Weather Grid information.
	- The lat and long go to a precision of 4.

## Development

### Build

Singular Build: `npm run build`

- Dev build
	- `tsc --watch`  
	- `npx tailwindcss -i ./css/tailwind.css -o ./css/tailwind_output.css --watch`

### The Stack

[PHP](https://www.php.net/) + [HTMX](https://htmx.org/) + [TailwindCSS](https://tailwindcss.com/) + [MariaDB](https://mariadb.com/)  
(Plus [NGINX](https://nginx.org/) as a reverse proxy.)  
(and [phpMyAdmin](https://www.phpmyadmin.net/) for ease of development.)

## Future Plans

???

## Shout-Outs

[Remix Icon](https://github.com/Remix-Design/RemixIcon) - Not used yet...
