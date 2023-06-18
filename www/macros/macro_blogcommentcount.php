<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."www/url.php");

function macro_blogcommentcount($args) {
    $topic_id = getattr($args, 'topic_id');

    if (is_null($topic_id)) {
        return macro_error('Expecting argument `topic_id`');
    }

    $query = sprintf("SELECT numReplies FROM ia_smf_topics
                      WHERE ID_TOPIC = %d", db_escape($topic_id));
    $res = db_fetch($query);

    $html = '<p style="text-align: right;">';
    $html .= '<img style="vertical-align: middle;" src="'.url_static('images/comment.png').'">';
    $html .= '&nbsp;<a href="'.IA_SMF_URL.'?topic='.$topic_id.'.0">Comentarii ('.$res['numReplies'].')</a>';
    $html .= '</p>';
    return $html;
}
