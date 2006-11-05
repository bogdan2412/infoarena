<?php 
    require("./SSI.php");

    if (!isset($_GET['topicID'])) return ;
    ssi_recentPostsFromTopic($_GET['topicID'], 
                             isset($_GET['num']) ? $_GET['num'] : 8);
 ?>