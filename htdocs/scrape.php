<?php
// 180s = 3 minutes
set_time_limit(180);

define('IN_FILE', true);
require('../include/general.inc.php');

if ($_GET['key'] != CONFIG_RSS_SCRAPE_KEY) {
    die; // TODO report error
}

require(CONFIG_ABS_PATH.'include/simplepie/autoloader.php');
require(CONFIG_ABS_PATH.'include/simplepie/idn/idna_convert.class.php');

// Create a new instance of the SimplePie object
$feed = new SimplePie();
// Force the given data/URL to be treated as a feed
$feed->force_feed(true);
// disable cache
$feed->enable_cache(false);

$feed->set_useragent('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0');

$res = $db->query('SELECT * FROM rss_feeds');

while($source = $res->fetch(PDO::FETCH_ASSOC)) {
    $feed->set_feed_url($source['url']);

    echo 'Getting feed from ', $source['url'], '<br />';

    // Initialize the whole SimplePie object.  Read the feed, process it, parse it, cache it, and
    // all that other good stuff.  The feed's information will not be available to SimplePie before
    // this is called.
    $success = $feed->init();

    // We'll make sure that the right content type and character encoding gets set automatically.
    // This function will grab the proper character encoding, as well as set the content type to text/html.
    $feed->handle_content_type();

    if ($feed->error()) {
        echo '<br />ERROR: ' , $feed->error(), '<br />';
        continue;
    }

    if (!$success) {
        echo '<br />NO SUCCESS: success: ', $success, '<br />';
        continue;
    }

    // general feed information
	echo $feed->get_link(), '<br />';
    echo $feed->get_title(), '<br />';
    echo $feed->get_description(), '<br />';

    // update the feed information
    $stmt = $db->prepare("UPDATE rss_feeds SET title = :title, description = :description, link = :link, refresh_last = UNIX_TIMESTAMP() WHERE id=:id");
    $stmt->execute(array(':title' => $feed->get_title(), ':description' => $feed->get_description(), ':link' => $feed->get_link(), ':id' => $source['id']));

    // get all the news stories
    foreach($feed->get_items() as $item) {

        // default to sane values
        $title = ($item->get_title() ? $item->get_title() : 'No title');
        $published = ($item->get_date('U') ? $item->get_date('U') : time());
        $permalink = ($item->get_permalink() ? $item->get_permalink() : '');
        $content = ($item->get_content() ? $item->get_content() : '');

        // insert into db
        $stmt = $db->prepare("INSERT IGNORE INTO rss_stories (hash_id, feed, added, published, title, permalink, content) VALUES (:hash_id, :feed, UNIX_TIMESTAMP(), :published, :title, :permalink, :content)");
        $stmt->execute(array(':hash_id' => md5($permalink), ':feed' => $source['id'], ':published' => $published, ':title' => $title, ':permalink' => $permalink, ':content' => $content));
        $affected_rows = $stmt->rowCount();
    }
}