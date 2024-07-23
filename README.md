# Calendar Track

This is a barebones PHP application for use as a display in front of a conference rooms (we opted to use a Raspbery Pi (3 | Model B) behind a PoE powered monitor)  
It was created with Google Calendar in mind, but should work for any calendar.

## Deployment

Docker Compose is the recommended way to deploy the application.  
I will eventually host a proper [dockerhub](https://hub.docker.com/) image, but Docker Compose is easier (and more familiar) to maintain for now.

### Required Configuration

1. Utilize the [Docker Compose file](docker-compose.yml)
2. Copy `./.example.env` to `./.env` and edit as necessary
3. Copy `./includes/config.example.php` to `./includes/config.php` and edit as necessary.
4. Copy either `php-development` or `php-production` as needed as `php.ini` in the `./docker/php/` folder. Edit as necessary.

### Management

Management can be handled through `/management/`.

This area allows you to set the weather from the DB (which can be overwritten via URL parameters), update the visible name, and update the ICS link.  
**Note:** The ICS link is never visible to the end-user, even within the management area. If you don't know what the current ICS link is for a given calendar either set it or check the database directly. :)

- The environment variable `CALENDAR_MANAGEMENT_PASSWORD_HASH` must be set. The management area will be disabled if this is empty.

### Parameters

The order of parameters does not matter.

| key               | required | value options                                                   | description                                                                                                                                                                                                                                                |
| ----------------- | -------- | --------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| id[]              | [X]      | Integers defining the ID of calendars that should be displayed. | Defines which calendars should be shown. This parameter can be passed multiple times in a given URL to show multiple calendars. The order in which values are passed determine how they will be shown.                                                                                                                                            |
| force-weather     | []       | `1` \| `0`                                                      | Bypasses the database setting for a given display. Ommitting the value falls back to the database setting (which is `0` by default).<br><br>**NOTE:** Weather must be enabled globally (via the `./includes/config.php` file) for this to have any effect. |
| horizontal-tiling | []       | `1` \| `0`                                                      | When using multiple calendars, this allows tiling to be done horizontally rather than vertically.                                                                                                                                                          |

### Weather

Weather is provided by the [National Oceanic and Atmospheric Administration](https://www.weather.gov/documentation/services-web-api) (api.weather.gov)

- Use `https://api.weather.gov/points/LAT,LONG` to find Weather Grid information.
  - The lat and long go to a precision of 4.
  
By default, this is disabled globally within the `./includes/config.php` file.

## How does it work?

A MySQL (MariaDB) database is created. This has a table that contains an ID, name, and Private ICS link.

Large calendars can take a while to get from Google. In my case, it would take 15-20 seconds just to get the calendar back from Google.
This ICS is parsed by [PHP ICS Parser](https://github.com/u01jmg3/ics-parser) which helps and the returned object is serialized and cached in the file system.

## Development

### Build

- Build
  - `tsc --watch`  

### The Stack

[PHP](https://www.php.net/) + [htmx](https://htmx.org/) + [MariaDB](https://mariadb.com/)  
(Plus [NGINX](https://nginx.org/) as a reverse proxy.)  
(and [phpMyAdmin](https://www.phpmyadmin.net/) for ease of development.)

## Future Plans

- Add support for multiple calendars on a single display, split horizontally.
- Finish calendar link management area.

## Shout-Outs ("Vendors")

No vendor work was modified, and all licensing is handled by the respective project.
A copy of each license is also avaiable in the [LICENSES](/LICENSES/) folder.

- [htmx](https://htmx.org/) - Front-end interactivity.
  - [BSD 2 Clause](https://opensource.org/license/bsd-2-clause/)
- [PHP ICS Parser](https://github.com/u01jmg3/ics-parser) - ICS Parsing and general handling.
  - [MIT](https://opensource.org/license/mit/)
- [Remix Icon](https://github.com/Remix-Design/RemixIcon) - Icons used for favicon and weather portion.
  - [Apache 2.0](https://www.apache.org/licenses/LICENSE-2.0)
