<?php

define('IN_FILE', true);
require('../include/general.inc.php');

enforceAuthentication();

head('User details');

if (isValidID($_GET['id'])) {

    $stmt = $db->prepare('SELECT * FROM users WHERE id=:user_id');
    $stmt->execute(array('user_id'=>$_GET['id']));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    sectionHead($user['username']);
    echo '<h3>Sex</h3> ', ($user['sex'] ? htmlspecialchars($user['sex']) : '<i>No sex</i>');

    echo '<h3>Description</h3> ', ($user['description'] ? htmlspecialchars($user['description']) : '<i>No description</i>');

    echo '<h3>Website</h3> ', ($user['website'] ? htmlspecialchars($user['website']) : '<i>No website</i>');

    if ($_SESSION['class'] > CONFIG_UC_MODERATOR) {
        echo '<h3>Passhash</h3> ', ($user['passhash'] ? htmlspecialchars($user['passhash']) : '<i>No passhash</i>');
    }
}

foot();