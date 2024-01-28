<?PHP
ini_set('memory_limit', '512M');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/globals.php');
require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/models.php');
$connection = new RTConnector(DB_TYPE, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, DB_CHARSET, DB_TRUST_CERT);
