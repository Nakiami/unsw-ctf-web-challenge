<?php

if (!defined('IN_FILE')) {
    die; // TODO report error
}

require('db.inc.php');

define('CONFIG_ABS_PATH', dirname(dirname(__FILE__)).'/');
define('CONFIG_FILE_UPLOAD_PATH', CONFIG_ABS_PATH . 'upload');

define('CONFIG_SITE_NAME', 'Bucky\'s pony appreciation society');
define('CONFIG_SITE_SLOGAN', '');
define('CONFIG_SITE_DESCRIPTION', '');

define('CONFIG_SUMMARY_LENGTH', 255);

define('CONFIG_INDEX_REDIRECT_TO', 'login.php');
define('CONFIG_LOGIN_REDIRECT_TO', 'index.php');
define('CONFIG_REGISTER_REDIRECT_TO', 'index.php');

define('CONFIG_HASH_SALT', 'H9zCxUs7wVHv9qNEP7r0FkEuJkWCCEQJVEqkVs2rAFPxNmk08FD2L0a1Yo8EaIM');

define('CONFIG_UC_USER', 0);
define('CONFIG_UC_MODERATOR', 100);

define('CONFIG_USER_ENABLED_ON_SIGNUP', false);

define('CONFIG_SSL_COMPAT', false);

define('CONFIG_MAX_FILE_UPLOAD_SIZE', 5242880);

define('CONFIG_MIN_PASSWORD_LENGTH', 6);
define('CONFIG_MIN_USERNAME_LENGTH', 3);

define('CONFIG_SESSION_ONLY_LOGIN', false);
define('CONFIG_COOKIE_EXPIRY', 2628000);
