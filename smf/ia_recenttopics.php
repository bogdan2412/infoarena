<?php

// Display recent topics in a given board.
// Used by infoarena's macro_smftopics.php

require("./SSI.php");

$count = min(getattr($_GET, 'count', 5), 10);
$board_id = getattr($_GET, 'board_id');

if ($board_id) {
    $board_url = IA_SMF_URL.'?board='.$board_id.'.0';
}
else {
    $board_url = IA_SMF_URL;
}

echo '<div class="smf recentTopics">';
echo '<div class="toolbar"><a href="'.$board_url.'">Vezi toate topic-urile</a></div>';
if (!$board_id) {
    ssi_recentTopics($count);
}
else {
    ssi_recentTopicsFromBoard($board_id, $count);
}
echo '</div>';

?>
