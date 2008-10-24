<?php
header("Content-Type: text/xml");
$optional = array();
// textInput, image, category and cloud don't work properly.. do not use them in your feed
$optional['channel'] = array('language', 'copyright', 'managingEditor',
                             'webMaster', 'pubDate', 'lastBuildDate',
                             'category', 'generator', 'docs', 'cloud', 'ttl',
                             'image', 'rating', 'textInput', 'skipHours',
                             'skipDays');

// category, enclosure, source don't work properly.. do not use them in your feed
$optional['item'] = array('author', 'pubDate', 'category', 'comments',
                          'enclosure', 'guid', 'source');
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
echo '<rss version="2.0">'."\n";
echo '<channel>'."\n";
echo '<title>'.html_escape(getattr($view['channel'], 'title')).'</title>'."\n";
echo '<link>'.html_escape(getattr($view['channel'], 'link')).'</link>'."\n";
echo '<description>'.html_escape(getattr($view['channel'], 'description')).'</description>'."\n";

foreach ($optional['channel'] as $hash_key => $hash_value) {
    if (getattr($view['channel'], $hash_value)) {
        echo '<'.$hash_value.'>';
        echo html_escape($view['channel'][$hash_value]);
        echo '</'.$hash_value.">\n";
    }
}

foreach ($view['item'] as $v) {
    echo '<item>'."\n";
    echo '<title>'.html_escape(getattr($v, 'title')).'</title>'."\n";
    echo '<link>'.html_escape(getattr($v, 'link')).'</link>'."\n";
    foreach ($optional['item'] as $hash_key => $hash_value) {
        if (getattr($v, $hash_value)) {
            echo '<'.$hash_value;
            if (is_array($v[$hash_value])) {
                foreach ($v[$hash_value] as $attr_name => $attr_value) {
                    if ($attr_name == 'value') {
                        continue;
                    }
                    echo ' '.html_escape($attr_name).'="'.html_escape($attr_value).'"';
                }
            }
            echo '>';
            if (is_array($v[$hash_value])) {
                echo html_escape($v[$hash_value]['value']);
            } else {
                echo html_escape($v[$hash_value]);
            }
            echo '</'.$hash_value.">\n";
        }
    }
    echo '<description>'.html_escape(getattr($v, 'description')).'</description>'."\n";
    echo '</item>'."\n";
}

echo '</channel>'."\n";
echo '</rss>'."\n";
?>
