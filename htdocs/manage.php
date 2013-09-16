<?php

define('IN_FILE', true);
require('../include/general.inc.php');

enforceAuthentication(CONFIG_UC_MODERATOR);

head('Site management');

sectionSubHead('Site management');
echo '<p>Your moderator key is: <strong>ec96a0f01efc61b76c7aeaf3072260d8787c985f95d3357d5042065425c5293e</strong></p>';
managementMenu();

if (isset($_GET['view'])) {
    sectionSubHead($_GET['view']);

    // security, bitch
    $_GET['view'] = substr($_GET['view'], 0, 23);

    // TODO FIXME
    // this might be dangerous but it's okay - only admins have access to this page anyway
    if (isset($_GET['debug']) && $_GET['debug'] == 1) {
        echo file_get_contents($_GET['view'].'.php');
    } else {
        require($_GET['view'].'.php');
    }

}

foot();