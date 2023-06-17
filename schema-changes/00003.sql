alter table ia_tags
  change `name` `name` varchar(128) not null,
  change `type` `type` enum('author','contest','year','round','age_group','method','algorithm','tag') not null,
  charset utf8mb4
  collate utf8mb4_romanian_ci;
