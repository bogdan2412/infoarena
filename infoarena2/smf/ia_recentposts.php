<?php 
    require("./SSI.php");

    ssi_recentPostsFromTopic($_GET['topicID'], 
                             isset($_GET['num']) ? $_GET['num'] : 8);
 ?>