<?php

define('IN_FILE', true);
require('../include/general.inc.php');

enforceAuthentication();

head('Users');
sectionHead('Users');

echo '
    <table id="files" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Username</th>
          <th>Member since</th>
          <th>Last login</th>
          <th>Class</th>
          <th>Contact</th>
        </tr>
      </thead>
      <tbody>
    ';

$stmt = $db->query('
    SELECT
    u.id,
    u.username,
    u.added,
    u.last_login,
    u.class,
    u.enabled
    FROM users AS u
    ORDER BY u.id ASC
    ');
while($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '
    <tr>
        <td><a href="user.php?id=',$user['id'],'">',htmlspecialchars($user['username']),'</a></td>
        <td>',getDateTime($user['added']),'</td>
        <td>',getDateTime($user['last_login']),'</td>
        <td>',getClassName($user['class']),'</td>
        <td><a href="messages.php?compose_to=',htmlspecialchars($user['id']),'" class="btn btn-mini btn-primary">Message</a></td>
    </tr>
    ';
}

echo '
      </tbody>
    </table>
     ';

foot();