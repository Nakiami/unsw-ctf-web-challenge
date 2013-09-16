<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */


define('IN_FILE', true);
require('../include/general.inc.php');

error_reporting(E_ALL | E_STRICT);
require(CONFIG_ABS_PATH . 'include/jQuery-File-Upload/UploadHandler.php');

$options = array('upload_dir' => CONFIG_ABS_PATH.'upload/', 'upload_url' => 'kekekekkeke', 'download_via_php' => true, 'script_url' => 'upload.php');

$upload_handler = new UploadHandler($options);