<?php 

$config['base_url'] = 'http://localhost/AppName'; // Base URL including trailing slash (e.g. http://localhost/)

$config['default_controller'] = 'main'; // Default controller to load
$config['error_controller'] = 'error'; // Controller used for errors (e.g. 404, 500 etc)

$config['db_host'] = ''; // Database host (e.g. localhost)
$config['db_name'] = ''; // Database name
$config['db_username'] = ''; // Database username
$config['db_password'] = ''; // Database password

	// LOCALE & TIMEZONE
 date_default_timezone_set('Europe/Lisbon');
 
 error_reporting( E_ALL | E_DEPRECATED);
	ini_set('display_errors', '1');
?> 