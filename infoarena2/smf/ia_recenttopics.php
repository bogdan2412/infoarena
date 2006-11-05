<?php 
    require("./SSI.php");

    if (!isset($_GET['boardID']) || $_GET['boardID'] == 0) {
        ssi_recentTopics(isset($_GET['num']) ? $_GET['num'] : 8);
    }
    else {
        ssi_recentTopicsFromBoard($_GET['boardID'], 
                                 isset($_GET['num']) ? $_GET['num'] : 8);
    }
 ?>