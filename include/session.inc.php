<?php

if (!defined('IN_FILE')) {
    exit(); // TODO report error
}

// This function must be called on all pages which require
// authentication. When this function is called, a visitor
// MUST be logged in be be allowed access.
function enforceAuthentication($minClass = CONFIG_UC_USER) {
    global $db;

    loginSessionRefresh();

    if (!$_SESSION['id']) {
        logout();
    }

    if ($_SESSION['class'] < $minClass) {
        logout();
    }

    // make sure admin is using an allowed IP
    if ($_SESSION['class'] > CONFIG_UC_USER) {

        $res = $db->prepare('SELECT INET_NTOA(ip) AS ip FROM admin_ip WHERE user_id=:user_id');
        $res->execute(array(':user_id'=>$_SESSION['id']));

        $valid = false;
        while ($entry = $res->fetch(PDO::FETCH_ASSOC)) {
            if ($entry['ip'] == getIP()) {
                $valid = true;
            }
        }

        if (!$valid) {

            sessionDataDestroy();
            cookieDataDestroy();

            errorMessage('Your IP is not one allowed to be used by admins. Goodbye.');
        }
    }
}

// This function must be called on all pages which use
// details from a users logged in session. It is not
// required for users to be logged in on pages where
// this function is called.
function loginSessionRefresh() {

    global $db;

    // deal with session only login
    if (CONFIG_SESSION_ONLY_LOGIN) {

        if (!$_SESSION['id']) {
            logout();
        }

        if ($_SESSION['fingerprint'] != getFingerPrint()) {
            logout();
        }

        session_regenerate_id(true);
    }

    // deal with cookie + session login
    else {

        // if we don't have a session ID or cookie set, just return
        // as we're clearly not logged in and not trying to be
        if (!$_SESSION['id'] && !$_COOKIE['id'] && !$_COOKIE['hash']) {
            return;
        }

        // we must have both cookie id and hash set to proceed
        // if one's missing, just log out as something's wrong
        if (!$_COOKIE['id'] || !$_COOKIE['hash']) {
            logout();
        }

        // fetch details of user with id given to us in the cookie
        $stmt = $db->prepare('SELECT id, passhash, salt, class, enabled FROM users WHERE id = :id');
        $stmt->bindParam(":id", $_COOKIE['id'], PDO::PARAM_INT); // reports error if not int
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // create the hash based on the details above
        $hash = makeCookieHash($user);

        // check if hash in cookie matches users details
        if ($hash != $_COOKIE['hash']) {
            logout();
        }

        // if all details are legit, create the session
        sessionDataCreate($user);
    }
}

function loginSessionCreate($postData) {

    global $db;

    $username = $postData[md5(CONFIG_SITE_NAME.'USR')];
    $password = $postData[md5(CONFIG_SITE_NAME.'PWD')];

    if(empty($username) || empty($password)) {
        genericMessage('Sorry', 'Please enter your username and password.');
    }

    $stmt = $db->prepare('SELECT id, passhash, salt, class, enabled, username FROM users WHERE username = :username');
    $stmt->execute(array(':username' => $username));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!checkPass($user['passhash'], $user['salt'], $password)) {
        errorMessage('Login failed');
    }

    if (!$user['enabled']) {
        genericMessage('Sorry', 'Your account is not enabled. Please contact the system administrator with any questions.');
    }

    // update most recent IP
    $stmt = $db->prepare('
        UPDATE users SET
        ip=INET_ATON(:ip),
        last_login=UNIX_TIMESTAMP()
        WHERE id=:id
    ');
    $stmt->execute(array(
        ':ip'=>getIP(),
        ':id'=>$user['id']
    ));

    if (CONFIG_SESSION_ONLY_LOGIN) {
        // creates a PHP session
        sessionDataCreate($user);
    } else {
        // create an offline "permanent" cookie
        cookieDataCreate($user);
        // create the server side session details
        sessionDataCreate($user);
    }

    return true;
}

function checkPass($hash, $salt, $password) {
    if ($hash == makePassHash($password, $salt)) {
        return true;
    }
    else {
        return false;
    }
}

function makePassHash($password, $salt) {
    return hash('sha256', $salt . $password . $salt . CONFIG_HASH_SALT);
}

function makeCookieHash ($user) {
    return hash('sha256', $user['id'].$user['class'].$user['passhash'].$user['salt']);
}

function makeSalt() {
    return hash('sha256', generateRandomString());
}

function sessionDataCreate ($user) {
    $_SESSION['id'] = $user['id'];
    $_SESSION['class'] = $user['class'];
    $_SESSION['enabled'] = $user['enabled'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['fingerprint'] = getFingerPrint();
}

function cookieDataCreate($user) {

    $expires = time() + CONFIG_COOKIE_EXPIRY;
    $hash = makeCookieHash($user);

    setcookie('id', $user['id'], $expires, '/');
    setcookie('hash', $hash, $expires, '/');
}

function getFingerPrint() {
    return md5(getIP());
}

function sessionDataDestroy () {
    session_unset();
    session_destroy();
}

function cookieDataDestroy() {
    setcookie('id', '', 0x7fffffff, '/');
    setcookie('hash', '', 0x7fffffff, '/');
}

function logout() {

    sessionDataDestroy();
    cookieDataDestroy();

    header('location: '.CONFIG_INDEX_REDIRECT_TO);
    exit();
}

function registerAccount($postData) {
    global $db;

    $username = $postData[md5(CONFIG_SITE_NAME.'USR')];
    $password = $postData[md5(CONFIG_SITE_NAME.'PWD')];

    if (empty($username) || empty($password)) {
        errorMessage('Please fill in all the details correctly.');
    }

    if (strlen($password) < CONFIG_MIN_PASSWORD_LENGTH) {
        errorMessage('Your password must be at least ' . CONFIG_MIN_PASSWORD_LENGTH . ' characters long.');
    }

    if (strlen($username) < CONFIG_MIN_USERNAME_LENGTH) {
        errorMessage('Your username must be at least ' . CONFIG_MIN_USERNAME_LENGTH . ' characters long.');
    }

    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        errorMessage('That doesn\'t look like an email. Please go back and double check the form.');
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE username=:username');
    $stmt->execute(array(':username' => $username));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user['id']) {
        errorMessage('An account with this username already exists.');
    }

    $stmt = $db->prepare('
    INSERT INTO users (
    username,
    passhash,
    salt,
    added,
    enabled
    ) VALUES (
    :username,
    :passhash,
    :salt,
    UNIX_TIMESTAMP(),
    '.(CONFIG_USER_ENABLED_ON_SIGNUP ? 1 : 0).'
    )
    ');

    $salt = makeSalt();
    $stmt->execute(array(
        ':username' => $username,
        ':salt' => $salt,
        ':passhash' => makePassHash($password, $salt),
    ));

    if ($stmt->rowCount()) {

        if (!CONFIG_USER_ENABLED_ON_SIGNUP) {
            genericMessage('Account creation successful!','Please wait until a mod wakes up to enable your account.
            Depending on your time zone, that could be hours.
            If you have any problems with your account, please quote your user ID to an administrator: '.$db->lastInsertId(), false);
        }

        return true;

    } else {
        return false;
    }
}