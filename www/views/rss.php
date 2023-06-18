<?php
header("Content-Type: text/xml; charset=utf-8");
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
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
echo '<channel>'."\n";
echo '<title>'.xml_escape(getattr($view['channel'], 'title')).'</title>'."\n";
echo '<atom:link rel="self" href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'" type="application/rss+xml">'."\n";
echo '<link>'.xml_escape(getattr($view['channel'], 'link')).'</link>'."\n";
echo '<description>'.xml_escape(getattr($view['channel'], 'description')).'</description>'."\n";

foreach ($optional['channel'] as $hash_key => $hash_value) {
    if (getattr($view['channel'], $hash_value)) {
        echo '<'.$hash_value.'>';
        echo xml_escape($view['channel'][$hash_value]);
        echo '</'.$hash_value.">\n";
    }
}

foreach ($view['item'] as $v) {
    echo '<item>'."\n";
    echo '<title>'.xml_escape(getattr($v, 'title')).'</title>'."\n";
    echo '<link>'.xml_escape(getattr($v, 'link')).'</link>'."\n";
    foreach ($optional['item'] as $hash_key => $hash_value) {
        if (getattr($v, $hash_value)) {
            echo '<'.$hash_value;
            if (is_array($v[$hash_value])) {
                foreach ($v[$hash_value] as $attr_name => $attr_value) {
                    if ($attr_name == 'value') {
                        continue;
                    }
                    echo ' '.xml_escape($attr_name).'="'.xml_escape($attr_value).'"';
                }
            }
            echo '>';
            if (is_array($v[$hash_value])) {
                echo xml_escape($v[$hash_value]['value']);
            } else {
                echo xml_escape($v[$hash_value]);
            }
            echo '</'.$hash_value.">\n";
        }
    }
    echo '<description>'.xml_escape(getattr($v, 'description')).'</description>'."\n";
    echo '</item>'."\n";
}

echo '</channel>'."\n";
echo '</rss>'."\n";
?>
