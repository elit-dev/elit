<?php
/*
 * PIP v0.5.3
 */

//Start the Session
session_start(); 

// Defines
define('ROOT_DIR', realpath(dirname(__FILE__)) .'/');
define('APP_DIR', ROOT_DIR .'application/');

// Includes
require(APP_DIR .'config/config.php');


require(ROOT_DIR .'system/Database.php');
require(APP_DIR . 'helpers/Session.php');
require(ROOT_DIR .'system/view.php');
require(ROOT_DIR .'system/controller.php');
require(ROOT_DIR .'system/jmarcel.php');

// Define base URL
global $config;
define('BASE_URL', $config['base_url']);
define('ASSETS_URL', $config['base_url'].'assets/'); // AssetsURL folder

jmarcel();

?>
