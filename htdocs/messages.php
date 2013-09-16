<?php

define('IN_FILE', true);
require('../include/general.inc.php');

enforceAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_POST['action'] == 'compose') {

        $res = $db->prepare('INSERT INTO messages (added, receiver_id, sender_id, subject, body) VALUES (UNIX_TIMESTAMP(), :receiver_id, :sender_id, :subject, :body)');
        $res->execute(array(
            ':receiver_id'=>$_POST['receiver_id'],
            ':sender_id'=>$_SESSION['id'],
            ':subject'=>$_POST['subject'],
            ':body'=>$_POST['body']
            ));

        genericMessage('Success','Message sent!');
    }
}

head('Message');

if (isset($_GET['compose_to']) && isValidID($_GET['compose_to'])) {
    sectionHead('Compose message');

        echo '
    <form class="form-horizontal" method="post">

        <div class="control-group">
            <label class="control-label" for="website">Subject</label>
            <div class="controls">
                <input type="text" id="subject" name="subject" class="input-block-level" placeholder="Subject" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="body"></label>
            <div class="controls">
                <textarea name="body" id="body" class="input-block-level"></textarea>
            </div>
        </div>

        <input type="hidden" name="receiver_id" value="',htmlspecialchars($_GET['compose_to']),'" />
        <input type="hidden" name="action" value="compose" />

        <div class="control-group">
            <label class="control-label" for="save"></label>
            <div class="controls">
                <button type="submit" id="save" class="btn btn-primary">Send</button>
            </div>
        </div>

    </form>';
}

else if (isset($_GET['view_id']) && isValidID($_GET['view_id'])) {
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
    sectionHead('Messages');

    echo '
        <table id="files" class="table table-striped table-hover">
          <thead>
            <tr>
              <th>Sender</th>
              <th>Subject</th>
              <th>Received</th>
              <th>Message preview</th>
            </tr>
          </thead>
          <tbody>
        ';

    $stmt = $db->prepare('
        SELECT
        u.username AS sender_username,
        m.id,
        m.subject,
        m.body,
        m.added
        FROM
        messages AS m LEFT JOIN users AS u ON m.sender_id = u.id
        WHERE m.receiver_id=:user_id
        ORDER BY m.id DESC
        ');
    $stmt->execute(array(':user_id'=>$_SESSION['id']));

    while($message = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '
        <tr>
            <td><a href="user.php?id=',$message['id'],'">',htmlspecialchars($message['sender_username']),'</a></td>
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