<?php
// Version
define('VERSION', '3.0.0.0');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// paultest
// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');
