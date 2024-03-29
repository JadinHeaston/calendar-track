<?PHP
//Configuration
define('APP_ROOT', '/');
////Calendar Management
define('CALENDAR_MANAGEMENT_PASSWORD_HASH', '113ea2fd2a5fb8dcc820ebfd29dce70cd3bb2aad6ac4bc6d8df51f03237356ede1ec1cfb9591029d0e65601215f609f967d63dba31ae5891809f64137c204a5e'); //"RANDOM_HASHED_PASSWORD"
define('CALENDAR_MANAGEMENT_HASH_ALGORITHM', 'sha512'); //https://www.php.net/manual/en/function.hash-algos.php
////Database
define('DB_HOST', 'ct-mariadb');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'calendar_track');
define('DB_TYPE', 'mysql');
define('DB_PORT', 3306);
define('DB_TRUST_CERT', 1);
define('DB_CHARSET', 'utf8mb4');
////File System Caching
define('FSCACHE_ICAL_CACHE_PERIOD', 900); //Seconds | How long the cached ICS files live for before reaching out to the provided ICS link.
define('FSCACHE_WEATHER_CACHE_PERIOD', 3600); //Seconds | How long weather cache files live for before reaching out to the weather API.
////UI
define('UI_DAY_RANGE', 15); //How many days to show from now. It is recommended to add 1 day to whatever range you want to visually see (1 week = 8 days). (Weather is unchanged by this)
//////Refresh
define('UI_FULL_PAGE_RELOAD', 86400); //Seconds (>30) | How frequently the entire page will be FULLY reloaded (refreshes cache). Be careful with low values.
define('UI_CALENDAR_UPDATE_RATE', 300); //Seconds | How frequently the front-end requests calendar updates from the server.
define('UI_WEATHER_UPDATE_RATE', 7200); //Seconds | How frequently the front-end requests weather updates from the server (weather API).
//////Dates - https://www.php.net/manual/en/datetime.format.php
define('UI_DATE_GROUP_HEADER', 'h:i:s A - l, F dS');
////////Events
define('UI_DATE_EVENT_HEADER', 'l - jS F o');
define('UI_DATE_EVENT_TIME', 'g:i A');
////Weather
define('WEATHER_ENABLE', false);
define('WEATHER_GRID_ID', 'LSX');
define('WEATHER_GRID_X', 19);
define('WEATHER_GRID_Y', 87);
////Miscellaneous
define('MISC_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0'); //Used for curl requests to the ICS file and weather API.
