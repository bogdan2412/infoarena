<?php
header("content-type: application/rss+xml\n\n");
$optional = array();
// textInput, image, category and cloud don't work properly.. do not use them in your feed
$optional['channel'] = array('language', 'copyright', 'managingEditor', 'webMaster', 'pubDate',
                             'lastBuildDate', 'category', 'generator', 'docs', 'cloud', 'ttl',
                             'image', 'rating', 'textInput', 'skipHours', 'skipDays');

// category, enclosure, source don't work properly.. do not use them in your feed
$optional['item'] = array('author', 'category', 'comments', 'enclosure', 'guid',
                          'pubDate', 'source');

echo '<?xml version="1.0"?>'."\n";
echo '<rss version="2.0">'."\n";
echo '<channel>'."\n";
echo '<title>'.htmlentities(getattr($view['channel'], 'titile')).'</title>'."\n";
echo '<link>'.htmlentities(getattr($view['channel'], 'link')).'</link>'."\n";
echo '<description>'.htmlentities(getattr($view['channel'], 'description')).'</description>'."\n";
foreach ($optional['channel'] as $hash_key => $hash_value) {
    if (getattr($view['channel'], $hash_value)) {
        echo '<'.$hash_value.'>';
        echo htmlentities($view['channel'][$hash_value]);
        echo '</'.$hash_value.'>';
    }
}

foreach ( $view['item'] as $v) {
    echo '<item>'."\n";
    echo '<title>'.htmlentities(getattr($v, 'title')).'</title>'."\n";
    echo '<link>'.htmlentities(getattr($v, 'link')).'</link>'."\n";
    echo '<description>'.htmlentities(getattr($v, 'description')).'</description>'."\n";
    /*foreach ($optional['item'] as $hash_key => $hash_value) {
        if (isset(getattr($v, $hash_key))) {
            echo '<'.$hash_key.'>';
            echo htmlentities($view['channel'][$hash_key]);
            echo '</'.$hash_key.'>';
        }
    }*/
    echo '</item>'."\n";
}

echo '</channel>'."\n";
echo '</rss>'."\n";
?>