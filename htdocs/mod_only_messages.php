<?php

enforceAuthentication(CONFIG_UC_MODERATOR);

if (isset($_GET['view_id']) && isValidID($_GET['view_id'])) {
    $stmt = $db->prepare('
        SELECT
        u.username AS sender_username,
        m.id,
        m.subject,
        m.body
        FROM
        messages AS m LEFT JOIN users AS u ON m.sender_id = u.id
        WHERE
        m.receiver_id=:user_id
        AND
        m.id=:message_id
        ');
    $stmt->execute(array(':user_id'=>$_SESSION['id'], ':message_id'=>$_GET['view_id']));
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    sectionHead($message['subject']);

    echo htmlspecialchars($message['body']);
}

else {
    echo '
        <table id="files" class="table table-striped table-hover">
          <thead>
            <tr>
              <th>Sender</th>
              <th>Receiver</th>
              <th>Subject</th>
              <th>Received</th>
              <th>Message preview</th>
            </tr>
          </thead>
          <tbody>
        ';

    $stmt = $db->query('
        SELECT
        u.username AS sender_username,
        u2.username AS receiver_username,
        m.id,
        m.subject,
        m.body,
        m.added
        FROM
        messages AS m
        LEFT JOIN users AS u ON m.sender_id = u.id
        LEFT JOIN users AS u2 ON m.receiver_id = u2.id
        GROUP BY m.id
        ORDER BY m.id DESC
        ');

    while($message = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '
        <tr>
            <td><a href="user.php?id=',$message['id'],'">',htmlspecialchars($message['sender_username']),'</a></td>
            <td><a href="user.php?id=',$message['id'],'">',htmlspecialchars($message['receiver_username']),'</a></td>
            <td><a href="messages.php?view_id=',$message['id'],'">',htmlspecialchars($message['subject']),'</a></td>
            <td>',getDateTime($message['added']),'</td>
            <td>',shortDescription($message['body'], 70),'</td>
        </tr>
        ';
    }

    echo '
          </tbody>
        </table>
         ';
}

foot();