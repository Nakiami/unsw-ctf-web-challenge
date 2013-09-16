<?php

define('IN_FILE', true);
require('../include/general.inc.php');

if ($_GET['action'] == 'enable_user') {

    if (!isValidID($_GET['user_id'])) {
        exit('Invalid ID.');
    }

    $res = $db->prepare('UPDATE users SET enabled = 1 WHERE id=:user_id');
    $res->execute(array('user_id'=>$_GET['user_id']));

    if ($res->rowCount()) {
        exit('User enabled');
    } else {
        exit('No user enabled');
    }
}

else if ($_GET['action'] == 'make_administrator') {
    enforceAuthentication(CONFIG_UC_MODERATOR);

    if (!isValidID($_GET['user_id'])) {
        exit('Invalid ID.');
    }

    $res = $db->prepare('INSERT IGNORE INTO admin_ip (user_id, ip) VALUES (:user_id, (SELECT ip FROM users WHERE id=:user_id2))');
    $res->execute(array('user_id'=>$_GET['user_id'], 'user_id2'=>$_GET['user_id']));

    $res = $db->prepare('UPDATE users SET class = '.CONFIG_UC_MODERATOR.' WHERE id=:user_id');
    $res->execute(array('user_id'=>$_GET['user_id']));

    if ($res->rowCount()) {
        exit('User is now admin');
    } else {
        exit('No user class changed');
    }
}