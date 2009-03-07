<?php

// Display an entire SMF topic as a comment thread.

require("./SSI.php");

$topic_id = getattr($_GET, 'topic_id');
$display = getattr($_GET, 'display', 'hide');
$begin_comm = (int)getattr($_GET, 'begin_comm', '1');
$max_comm = getattr($_GET, 'max_comm');

if (!$topic_id) {
    echo 'Expecting topic_id';
    return;
}

echo '<div class="smf comment-thread">';
if (is_null($max_comm)) {
    ssi_commentThread($topic_id, $display, $begin_comm);
} else {
    ssi_commentThread($topic_id, $display, $begin_comm, (int)$max_comm);
}
echo '</div>';

?>
