<?php
//Loader, with varying connection that lacks database specification.
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/enumerators.php');
require_once(__DIR__ . '/../includes/globals.php');
require_once(__DIR__ . '/../includes/functions.php');
require_once(__DIR__ . '/../includes/models.php');

require_once(__DIR__ . '/setup_functions.php');

$setupConnection = new RTConnector(DB_TYPE, DB_HOST, DB_PORT, '', DB_USERNAME, DB_PASSWORD, DB_CHARSET, DB_TRUST_CERT);

$setupConnection->executeStatement(file_get_contents(__DIR__ . '/../sql/initial_table_setup.sql'), [], true);
$setupConnection->executeStatement(file_get_contents(__DIR__ . '/../sql/initial_table_values.sql'), [], true);
