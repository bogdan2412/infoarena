<?php

// Display recent posts in a topic.
// Used by infoarena's macro_smftopic.php

require("./SSI.php");

$count = min(getattr($_GET, 'count', 5), 10);
$topic_id = getattr($_GET, 'topic_id');

if (!$topic_id) {
    echo 'Expecting topic_id';
    return;
}

echo '<div class="smf recentPosts">';
echo '<div class="toolbar"><a href="'.IA_SMF_URL.'?topic='.$topic_id.'.0">Vezi intreg topic-ul</a></div>';
ssi_recentPostsFromTopic($topic_id, $count);
echo '</div>';

?>
