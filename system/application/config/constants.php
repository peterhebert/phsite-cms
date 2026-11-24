<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 					'ab');
define('FOPEN_READ_WRITE_CREATE', 				'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 			'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/*
|--------------------------------------------------------------------------
| phSite CMS
|--------------------------------------------------------------------------
|
| These constants are custom to the phSite CMS application
|
*/
// set your uri word separator - default is dash '-' (SEO-friendly),
// but you could use an underscore '_' if you like
define('URI_SEPARATOR', '-');

// salt length for password hashes - DO NOT CHANGE!
define('SALT_LENGTH', 10);

// server path of install without trailing slash
define('ROOT_REL','/'.trim(FCPATH,'/'));
// path of install relative to document root
define('BASE_PATH',preg_replace("#^(.*)/index.php(.*)#", "$1", $_SERVER['PHP_SELF']));

// document root relative path to media files
define('MEDIA_PATH_ORIG', '/media/originals');
define('MEDIA_PATH_CACHE', '/media/cache');
define('PHSITE_CMS_VERSION', "0.3.1");

// development mode for CMS admin panel
// TRUE = use individual uncompressed js and css files, show debugging messages
// FALSE = use concatenated and minified js and css files
define('DEV_MODE', FALSE);

/* End of file constants.php */
/* Location: ./system/application/config/constants.php */