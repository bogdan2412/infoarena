<?php

require_once(IA_ROOT."common/db/db.php");

// News rss.
// FIXME: stupid hack.
function controller_news_feed($page_name) {
    $view = array();
    $view['channel']['title'] = 'Stiri infoarena';
    $view['channel']['link'] = url_absolute(url_textblock('news'));
    $view['channel']['description'] = 'Ultimele stiri de pe http://infoarena.ro';
    $view['channel']['language'] = 'ro-ro';
    $view['channel']['copyright'] = '&copy; 2006 - Asociatia infoarena';

    $news = news_get_range(0, IA_MAX_FEED_ITEMS);
    for ($i = 0; $i < count($news); $i++) {
        $view['item'][$i]['title'] = $news[$i]['title'];
        $view['item'][$i]['description'] = wiki_process_text_recursive($news[$i]);
        $view['item'][$i]['pubDate'] = date('r',
                                            strtotime($news[$i]['timestamp']));
        $view['item'][$i]['guid'] = sha1($news[$i]['name'] . 
                                         $news[$i]['timestamp']);

        // since *some* RSS readers mark items as read according to LINK
        // rather than GUID, make sure every change to a news article yields
        // a unique link
        $view['item'][$i]['link'] = url_absolute(
                url_textblock($news[$i]['name'])).
                '#'.$view['item'][$i]['guid'];
    }

    execute_view_die('views/rss.php', $view);
}

?>
