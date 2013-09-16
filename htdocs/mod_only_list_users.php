<?php

enforceAuthentication(CONFIG_UC_MODERATOR);

echo '
    <table id="files" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Username</th>
          <th>Added</th>
          <th>Class</th>
          <th>Enabled</th>
          <th>Contact</th>
          <th>Edit</th>
        </tr>
      </thead>
      <tbody>
    ';

$stmt = $db->query('
    SELECT
    u.id,
    u.username,
    u.added,
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
        <td>',getClassName($user['class']),'</td>
        <td>',($user['enabled'] ? 'Yes' : 'No'),'</td>
        <td><a href="messages.php?compose_to=',htmlspecialchars($user['id']),'" class="btn btn-mini btn-primary">Message</a></td>
        <td><a href="mod_only_edit_user.php?id=',htmlspecialchars($user['id']),'" class="btn btn-mini btn-warning">Edit</a></td>
    </tr>
    ';
}

echo '
      </tbody>
    </table>
     ';

foot();