<?php 
    require("./SSI.php");

    if (isset($_GET['num'])) {
        $num = min($_GET['num'], 100);
    }
    else {
        $num = 8;
    }

    if (!isset($_GET['topicID'])) return ;

    ssi_recentPostsFromTopic($_GET['topicID'], $num);
 ?>