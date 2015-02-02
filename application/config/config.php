<?php 

$config['base_url'] = 'http://localhost/jmarcel/'; // Base URL including trailing slash (e.g. http://localhost/)

$config['default_controller'] = 'site'; // Default controller to load
$config['error_controller'] = 'error'; // Controller used for errors (e.g. 404, 500 etc)

$config['db_host'] = 'localhost'; // Database host (e.g. localhost)
$config['db_name'] = 'assist2014'; // Database name
$config['db_username'] = 'root'; // Database username
$config['db_password'] = 'root'; // Database password

	// LOCALE & TIMEZONE
 date_default_timezone_set('Europe/Lisbon');
 
 error_reporting( E_ALL | E_DEPRECATED);
	ini_set('display_errors', '1');
?> 