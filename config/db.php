<?php
/**
 * Database Configuration
 *
 * All of your system's database connection settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/DbConfig.php.
 */

$dbhost = '';
$dbusername = '';
$dbpassword = '';
$dbname = '';

foreach ($_SERVER as $key => $value) {
    if (strpos($key, "MYSQLCONNSTR_localdb") !== 0) {
        continue;
    }
    
    $dbhost = preg_replace("/^.*Data Source=(.+?);.*$/", "\\1", $value);
    $dbusername = preg_replace("/^.*User Id=(.+?);.*$/", "\\1", $value);
    $dbpassword = preg_replace("/^.*Password=(.+?)$/", "\\1", $value);
    $dbname = "taitaja";
}

if (empty($dbhost)) $dbhost = getenv('DB_SERVER');
if (empty($dbusername)) $dbusername = getenv('DB_USER');
if (empty($dbpassword)) $dbpassword = getenv('DB_PASSWORD');
if (empty($dbname)) $dbname = getenv('DB_DATABASE');

return [
    'driver' => "mysql",
    'server' => $dbhost,
    'user' => $dbusername,
    'password' => $dbpassword,
    'database' => $dbname,
    'schema' => "public",
    'tablePrefix' => ""
];
