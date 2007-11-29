<?php

// Display an entire SMF topic as a comment thread.

require("./SSI.php");

$topic_id = getattr($_GET, 'topic_id');

if (!$topic_id) {
    echo 'Expecting topic_id';
    return;
}

echo '<div class="smf comment-thread">';
ssi_commentThread($topic_id);
echo '</div>';

?>
