<?php

if (!defined('IN_FILE')) {
    exit(); // TODO report error
}

function head($title = '') {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><? echo ($title ? htmlspecialchars($title) . ' : ' : '') , CONFIG_SITE_NAME, ' - ', CONFIG_SITE_SLOGAN ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<? echo CONFIG_SITE_DESCRIPTION ?>">
    <meta name="author" content="">

    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Fav and touch icons -->
    <link rel="icon" href="img/favicon.png" type="image/png" />

    <script type="text/javascript" src="js/magic.js"></script>

</head>

<body>

<div class="container">

    <div class="masthead">
        <h3 class="muted"><img src="http://i.imgur.com/kIEPAGu.png" /> <? echo CONFIG_SITE_NAME ?></h3>

<div class="navbar">
    <div class="navbar-inner">
        <div class="container">
            <ul class="nav">
                <?php
                if ($_SESSION['id']) {

                    // Logged in menu start

                    if ($_SESSION['class'] >= CONFIG_UC_MODERATOR) {
                        // Moderator menu start
                        echo '<li',(getRequestedFileName() == 'manage' ? ' class="active"' : ''),'><a href="manage.php">Manage</a></li>';
                        // Moderator menu end
                    }
                ?>
                    <li<?php echo (getRequestedFileName() == 'index' ? ' class="active"' : '') ?>><a href="index.php">Home</a></li>
                    <li<?php echo (getRequestedFileName() == 'messages' ? ' class="active"' : '') ?>><a href="messages.php">Messages</a></li>
                    <li<?php echo (getRequestedFileName() == 'users' ? ' class="active"' : '') ?>><a href="users.php">Users</a></li>
                    <li<?php echo (getRequestedFileName() == 'control' ? ' class="active"' : '') ?>><a href="control.php">Control panel</a></li>
                    <li<?php echo (getRequestedFileName() == 'logout' ? ' class="active"' : '') ?>><a href="logout.php">Log out</a></li>
                <?php
                    // Logged in menu end
                } else {
                    // Guest menu start
                ?>
                    <li<?php echo (getRequestedFileName() == 'login' ? ' class="active"' : '') ?>><a href="login.php">Log in / Register</a></li>
                <?php
                    // Guest menu end
                }
                ?>
            </ul>
        </div>
    </div>
</div><!-- /.navbar -->
</div>

<div id="secret" class="alert alert-success">
    <h1>5s1780o49o9o0rq5oq4on8n0q4sp49r2nr</h1>
</div>

    <?php

    if (isset($_GET['generic_success'])) {
        echo '
        <div class="alert alert-success">
        Success!
        </div>
        ';
    } else if (isset($_GET['generic_warning'])) {
        echo '
        <div class="alert alert-warning">
        Something failed!
        </div>
        ';
    }
}

function foot () {
   echo '
    <hr>

    <div class="footer">

    </div>

</div> <!-- /container -->
'.(getIP() != '127.0.0.1' && !$_SESSION['id'] ?
'<audio autoplay loop>
    <source src="intro.ogg" type="audio/ogg" preload="auto" autoplay="autoplay">
</audio>' : ''
).'

</body>
</html>';
}

function sectionHead ($title) {
    echo '<div class="page-header"><h2>',htmlspecialchars($title),'</h2></div>';
}

function sectionSubHead ($title, $strip_html = true) {
    echo '<div class="page-header"><h1><small>',($strip_html ? htmlspecialchars($title) : $title),'</small></h1></div>';
}

function partitionStart() {
    echo '
    <div class="row-fluid show-grid">
        <div class="span12">';
}

function partitionEnd() {
    echo '
        </div>
    </div>';
}

function errorMessage ($message, $head = true, $foot = true, $exit = true) {
    if ($head) {
        head('Error');
    }

    echo '<div class="alert alert-error"><strong>Error</strong> <p>'.htmlspecialchars($message).'</p></div>';

    if ($foot) {
        foot();
    }

    if ($exit) {
        exit();
    }
}

function genericMessage ($title, $message, $head = true, $foot = true, $exit = true) {
    head($title);

    echo '<div class="alert alert-info"><strong>',htmlspecialchars($title),'</strong> <p>'.htmlspecialchars($message).'</p></div>';

    if ($foot) {
        foot();
    }

    if ($exit) {
        exit();
    }
}

function managementMenu () {
    echo '
        <a href="?view=mod_only_list_users" class="btn btn-primary">List users</a>
        <a href="?view=mod_only_messages" class="btn btn-primary">List messages</a>
        <a href="?view=mod_only_site_log&debug=1" class="btn btn-primary">View server problem log</a>
    ';
}