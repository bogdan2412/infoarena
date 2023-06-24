<?php

require_once(IA_ROOT_DIR . "common/db/textblock.php");
require_once(IA_ROOT_DIR . "common/db/tags.php");
require_once(IA_ROOT_DIR . "common/db/blog.php");
require_once(IA_ROOT_DIR . "common/textblock.php");
require_once(IA_ROOT_DIR . "lib/Wiki.php");
require_once(IA_ROOT_DIR . "www/format/pager.php");
require_once(IA_ROOT_DIR . "www/wiki/wiki.php");

function controller_blog_feed() {
    $view = array();
    $view['channel']['title'] = 'Blog infoarena';
    $view['channel']['link'] = url_absolute(url_blog());
    $view['channel']['description'] = 'Ultimele însemnări de pe blog-ul infoarena';
    $view['channel']['language'] = 'ro-ro';
    $view['channel']['copyright'] = '(c) 2008 - Asociația infoarena';

    $blog = blog_get_range(null, 0, IA_MAX_FEED_ITEMS);
    for ($i = 0; $i < count($blog); $i++) {
        $view['item'][$i]['title'] = strip_tags($blog[$i]['title']);
        $view['item'][$i]['description'] = wiki_process_textblock_recursive($blog[$i]);
        $view['item'][$i]['pubDate'] = date('r', strtotime($blog[$i]['creation_timestamp']));
        $view['item'][$i]['guid']['value'] = sha1($blog[$i]['name'].$blog[$i]['creation_timestamp']);
        $view['item'][$i]['guid']['isPermaLink'] = 'false';

        // since *some* RSS readers mark items as read according to LINK
        // rather than GUID, make sure every change to a blog article yields
        // a unique link
        $view['item'][$i]['link'] = url_absolute(url_textblock($blog[$i]['name'])).'#'.$view['item'][$i]['guid']['value'];
    }

    execute_view_die('views/rss.php', $view);
}

function controller_blog_index() {
    // Build view
    $view = array();
    $view['topnav_select'] = 'blog';
    $view['title'] = 'infoarena - Blog';

    // Pager options
    $args['display_entries'] = request('display_entries', 10);
    $args['param_prefix'] = 'blog_';
    $view['options'] = pager_init_options($args);
    $view['options']['show_count'] = true;

    // Get blog posts
    $view['tag'] = request('tag', "");
    $view['subpages'] = blog_get_range($view['tag'], $view['options']['first_entry'], $view['options']['display_entries']);
    $view['options']['total_entries'] = blog_count($view['tag']);

    // Get some extra info for blog posts
    foreach ($view['subpages'] as &$subpage) {
        $first_textblock = textblock_get_revision($subpage['name'], 1, true);
        $subpage['user_name'] = $first_textblock['user_name'];
        $subpage['user_fullname'] = $first_textblock['user_fullname'];
        $subpage['rating_cache'] = $first_textblock['rating_cache'];
        $subpage['forum_topic'] = blog_get_forum_topic($subpage['name']);
        $subpage['comment_count'] = blog_get_comment_count($subpage['forum_topic']);
        $subpage['tags'] = tag_get("textblock", $subpage['name']);
    }

    execute_view_die('views/blog_index.php', $view);
}

function controller_blog_view($page_name, $rev_num = null) {
    // Get actual page.
    $crpage = textblock_get_revision($page_name, null, true);

    // If the page is missing jump to the edit/create controller.
    if ($crpage) {
        // FIXME: hack to properly display latest revision.
        // Checks if $rev_num is the latest.
        $rev_count = textblock_get_revision_count($page_name);
        if ($rev_num && $rev_num != $rev_count) {
            identity_require("textblock-history", $crpage);
            $page = textblock_get_revision($page_name, $rev_num, true);

            if (!$page) {
                flash_error("Revizia \"{$rev_num}\" nu există.");
                $page = $crpage;
            }
        } else {
            identity_require("textblock-view", $crpage);
            $page = $crpage;
        }
    } else {
        // Missing page.
        flash_error("Nu există pagina, dar poți sa o creezi.");
        redirect(url_textblock_edit($page_name));
    }

    log_assert_valid(textblock_validate($page));

    if (!IA_DEVELOPMENT_MODE) {
        // FIXME: add meta description to each blog post
        // FIXME: extend to textblocks?
        $meta_info = array();
        // Facebook like meta data
        $meta_info[] = array('property' => 'og:title', 'content' => $page['title']);
        $meta_info[] = array('property' => 'og:type', 'content' => 'blog');
        $meta_info[] = array('property' => 'og:url', 'content' => url_absolute(url_textblock($page['name'])));
        $meta_info[] = array('property' => 'og:image', 'content' => url_absolute(url_static('images/icon-user-64.gif')));
        $meta_info[] = array('property' => 'og:site_name', 'content' => 'infoarena.ro');
        $meta_info[] = array('property' => 'fb:app_id', 'content' => IA_FACEBOOK_APP_ID);

        // General meta data
        $meta_info[] = array('name' => 'title', 'content' => $page['title']);
    }


    // Build view.
    $view = array();
    $view['topnav_select'] = 'blog';
    $view['title'] = $page['title'];
    $view['revision'] = $rev_num;
    $view['revision_count'] = $rev_count;
    $view['page_name'] = $page['name'];
    $view['textblock'] = $page;
    $view['forum_topic'] = $page['forum_topic'];
    $view['tags'] = tag_get("textblock", $page['name']);
    $view['first_textblock'] = textblock_get_revision($page_name, 1, true);

    if (!IA_DEVELOPMENT_MODE) {
        $view['meta_info'] = $meta_info;
    }
    // This emits a warning on the stripped database used by devs because
    // some revisions are missing. On the live database it should not.
    if ($view['textblock']['creation_timestamp'] !=
            $view['first_textblock']['timestamp']) {
        log_warn("Inconsistent database: first revision missing");
    }


    execute_view_die('views/blog_view.php', $view);
}

?>
