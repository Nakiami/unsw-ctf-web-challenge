<?php
define('IN_FILE', true);
require('../include/general.inc.php');

enforceAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_POST['action'] == 'post') {

        $res = $db->prepare('INSERT INTO forum (body, user_id) VALUES (:body, :user_id)');
        $res->execute(array(
            ':body'=>$_POST['body'],
            ':user_id'=>$_SESSION['id']
        ));

        header('location: index.php');
        exit();
    }
}

head();

$tile_width_count = 12;
$res_tile = $db->prepare('
    SELECT
    t.*,
    tp.top_px,
    tp.left_px
    FROM
    tile_tiles AS t LEFT JOIN tile_position AS tp ON t.id = tp.tile_id AND tp.user_id=:user_id
');
$res_tile->execute(array(':user_id'=>$_SESSION['id']));
while($tile = $res_tile->fetch(PDO::FETCH_ASSOC)) {

    if ($tile_width_count >= 12) {
        echo '
            <div class="row-fluid show-grid">
        ';
        $tile_width_count -= 12;
    }

    echo '
        <div class="span',htmlspecialchars($tile['width']),'" id="',htmlspecialchars($tile['id']),'">
    ';

    if ($tile['type'] == 'rss') {
        echo '
                <ul>
        ';
                $res = $db->prepare('
                  SELECT
                  rs.title,
                  rs.published,
                  rs.permalink,
                  rs.content
                  FROM rss_stories AS rs JOIN rss_feeds AS rf ON rs.feed = rf.id
                  WHERE rf.tile_id=:tile_id
                  ORDER BY published DESC
                  LIMIT 10
                ');
                $res->execute(array(':tile_id'=>$tile['id']));

                while($rss = $res->fetch(PDO::FETCH_ASSOC)) {
                    echo '
                    <li>
                        <small>',date('D, H:m',$rss['published']),'</small>
                        <a href="',htmlspecialchars($rss['permalink']),'">',htmlspecialchars($rss['title']),'</a>
                    </li>';
                }
        echo '
                </ul>
        ';
    }

    else if ($tile['type'] == 'post') {

        $res = $db->prepare('SELECT * FROM posts WHERE tile_id=:tile_id');
        $res->execute(array(':tile_id'=>$tile['id']));

        while($post = $res->fetch(PDO::FETCH_ASSOC)) {
            echo '
                <h1>',htmlspecialchars($post['title']),'</h1>
                ',htmlspecialchars($post['body']),'
            ';
        }
    }

    else if ($tile['type'] == 'content') {

        $res = $db->prepare('SELECT * FROM content WHERE tile_id=:tile_id');
        $res->execute(array(':tile_id'=>$tile['id']));

        while($post = $res->fetch(PDO::FETCH_ASSOC)) {
            echo '
                ',($post['body']),'
            ';
        }
    }

    echo '
    </div>
    ';

    if ($tile['top_px'] || $tile['left_px']) {
        echo '
        <script>
            $("#',htmlspecialchars($tile['id']),'").animate({
                "marginTop" : "+=',htmlspecialchars($tile['top_px']),'px",
                "marginLeft" : "+=',htmlspecialchars($tile['left_px']),'px"
            });
        </script>
        ';
    }

    $tile_width_count += $tile['width'];

    if ($tile_width_count >= 12) {
        echo '
            </div>
        ';
    }
}

echo '
<div class="span5" id="',htmlspecialchars($tile['id']),'">
<form action="" method="post">
<textarea name="body" class="input-block-level"></textarea>
<input type="hidden" name="action" value="post">
<button type="submit" id="save" class="btn btn-primary">Post</button>
</form>
</div>
';

$tile_width_count += 5;

$res = $db->query('
    SELECT
    u.username,
    p.title,
    p.body
    FROM forum AS p LEFT JOIN users AS u ON p.user_id = u.id
    ORDER BY p.id DESC
');
while($tile = $res->fetch(PDO::FETCH_ASSOC)) {

    if ($tile_width_count >= 12) {
        echo '
            <div class="row-fluid show-grid">
        ';
        $tile_width_count -= 12;
    }

    echo '
        <div class="span3" id="',htmlspecialchars($tile['id']),'">
    ';

    echo $tile['username'] . ' neighs: ' . nl2br(htmlspecialchars($tile['body']));

    echo '
    </div>
    ';

    if ($tile['top_px'] || $tile['left_px']) {
        echo '
        <script>
            $("#',htmlspecialchars($tile['id']),'").animate({
                "marginTop" : "+=',htmlspecialchars($tile['top_px']),'px",
                "marginLeft" : "+=',htmlspecialchars($tile['left_px']),'px"
            });
        </script>
        ';
    }

    $tile_width_count += 3;

    if ($tile_width_count >= 12) {
        echo '
            </div>
        ';
    }
}

foot();