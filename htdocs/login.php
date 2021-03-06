<?php

define('IN_FILE', true);
require('../include/general.inc.php');

if ($_SESSION['id']) {
    header('location: ' . CONFIG_LOGIN_REDIRECT_TO);
    exit();
}

forceSSL();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'login') {
        if (loginSessionCreate($_POST)) {
            header('location: ' . CONFIG_LOGIN_REDIRECT_TO);
        } else {
            errorMessage('Login failed? Helpful.');
        }
    }

    else if ($_POST['action'] == 'register') {
        if (registerAccount($_POST) && loginSessionCreate($_POST)) {
            header('location: ' . CONFIG_REGISTER_REDIRECT_TO);
        } else {
            errorMessage('Sign up failed? Helpful.');
        }
    }

    exit();
}

head('Login');
?>

<form method="post" class="form-signin">
    <h3 class="form-signin-heading">Please sign in</h3>
    <input name="<? echo md5(CONFIG_SITE_NAME.'USR') ?>" type="text" class="input-block-level" placeholder="Email address">
    <input name="<? echo md5(CONFIG_SITE_NAME.'PWD') ?>" type="password" class="input-block-level" placeholder="Password">
    <input type="hidden" name="action" value="login" />
    <button class="btn btn-primary" type="submit">Sign in</button>
</form>

<form method="post" class="form-signin">
    <h3 class="form-signin-heading">Neigh have an account? Sign up!</h3>
    <input name="<? echo md5(CONFIG_SITE_NAME.'USR') ?>" type="text" class="input-block-level" placeholder="Email address">
    <input name="<? echo md5(CONFIG_SITE_NAME.'PWD') ?>" type="password" class="input-block-level" placeholder="Password">
    <input type="hidden" name="action" value="register" />
    <button class="btn btn-primary" type="submit">Brohoof</button>
</form>

<?php
foot();