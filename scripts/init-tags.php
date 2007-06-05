#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR."common/db/user.php");

ini_set("memory_limit", "128M");

db_connect();

$query = "DROP TABLE IF EXISTS `ia_tags`";
db_query($query);
$query = "CREATE TABLE `ia_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_general_ci;";
db_query($query);


$query = "DROP TABLE IF EXISTS `ia_user_tags`";
db_query($query);
$query = "CREATE TABLE `ia_user_tags` (
  `tag_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL, 
  PRIMARY KEY (`tag_id`, `user_id`), KEY (`user_id`, `tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_general_ci;";
db_query($query);

$query = "DROP TABLE IF EXISTS `ia_round_tags`";
db_query($query);
$query = "CREATE TABLE `ia_round_tags` (
  `tag_id` int(11) NOT NULL,
  `round_id` varchar(64) default NULL, 
  PRIMARY KEY (`tag_id`, `round_id`), KEY (`round_id`, `tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_general_ci;";
db_query($query);

$query = "DROP TABLE IF EXISTS `ia_task_tags`";
db_query($query);
$query = "CREATE TABLE `ia_task_tags` (
  `tag_id` int(11) NOT NULL,
  `task_id` varchar(64) default NULL, 
  PRIMARY KEY (`tag_id`, `task_id`), KEY (`task_id`, `tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_general_ci;";
db_query($query);

$query = "DROP TABLE IF EXISTS `ia_textblock_tags`";
db_query($query);
$query = "CREATE TABLE `ia_textblock_tags` (
  `tag_id` int(11) NOT NULL,
  `textblock_id` varchar(64) default NULL, 
  PRIMARY KEY (`tag_id`, `textblock_id`), KEY (`textblock_id`, `tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_general_ci;";
db_query($query);
?>
