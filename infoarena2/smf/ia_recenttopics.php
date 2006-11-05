<?php 
    require("./SSI.php");

    if (isset($_GET['num'])) {
        $num = min($_GET['num'], 100);
    }
    else {
        $num = 8;
    }

    if (!isset($_GET['boardID']) || $_GET['boardID'] == 0) {
        ssi_recentTopics($num);
    }
    else {
        ssi_recentTopicsFromBoard($_GET['boardID'], $num);
    }
 ?>