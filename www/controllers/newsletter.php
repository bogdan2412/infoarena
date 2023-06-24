<?php

require_once(IA_ROOT_DIR . 'common/db/tags.php');
require_once(IA_ROOT_DIR . 'common/db/textblock.php');
require_once(IA_ROOT_DIR . 'common/textblock.php');
require_once(IA_ROOT_DIR . 'www/controllers/textblock.php');
require_once(IA_ROOT_DIR . 'lib/Wiki.php');

// Newsletter index. Display list of all newsletters.
function controller_newsletter_index() {
    $view = array();
    $view['title'] = 'Newsletter infoarena';
    $view['letters'] = textblock_get_by_prefix(IA_NEWSLETTER_TEXTBLOCK_PREFIX,
            false, true, 'desc');
    execute_view_die('views/newsletter_index.php', $view);
}

// Display body of one newsletter.
// This is a wrapper around controller_textblock_view(...)
function controller_newsletter_preview_body($newsletter_id, $rev_num = null) {
    controller_textblock_view(IA_NEWSLETTER_TEXTBLOCK_PREFIX.$newsletter_id,
        $rev_num, 'views/newsletter_preview_body.php');
}

// Preview frame for one newsletter. This shows an iframe that contains
// the newsletter body.
// This is a wrapper around controller_textblock_view(...)
function controller_newsletter_preview_frame($newsletter_id, $rev_num = null) {
    controller_textblock_view(IA_NEWSLETTER_TEXTBLOCK_PREFIX.$newsletter_id,
        $rev_num, 'views/newsletter_preview_frame.php');
}

?>
