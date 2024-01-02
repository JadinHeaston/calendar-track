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

[PHP](https://www.php.net/) + [htmx](https://htmx.org/) + [TailwindCSS](https://tailwindcss.com/) + [MariaDB](https://mariadb.com/)  
(Plus [NGINX](https://nginx.org/) as a reverse proxy.)  
(and [phpMyAdmin](https://www.phpmyadmin.net/) for ease of development.)

## Future Plans

???

## Shout-Outs ("Vendors")

No vendor work was modified, and all licensing is handled by the respective project.
A copy of each license is also avaiable in the [LICENSES](/LICENSES/) folder.

- [htmx](https://htmx.org/) - Front-end interactivity.
	- [BSD 2 Clause](https://opensource.org/license/bsd-2-clause/)
- [PHP ICS Parser](https://github.com/u01jmg3/ics-parser) - ICS Parsing and general handling.
	- [MIT](https://opensource.org/license/mit/)
- [Remix Icon](https://github.com/Remix-Design/RemixIcon) - Icons (Not used yet)
	- [Apache 2.0](https://www.apache.org/licenses/LICENSE-2.0)
