-- Migration script from the old Vianuarena schema (circa September 2012)
-- to the current schema (December 2018).

-- This does not populate fields, it only adds tables, fields, indexes and
-- enum values.

DROP TABLE IF EXISTS ia_acm_round;
CREATE TABLE ia_acm_round (
  user_id int(11) NOT NULL,
  round_id varchar(64) NOT NULL,
  task_id varchar(64) NOT NULL,
  score int(11) DEFAULT NULL,
  penalty int(11) DEFAULT NULL,
  submission int(11) DEFAULT NULL,
  partial_score int(11) DEFAULT NULL,
  partial_penalty int(11) DEFAULT NULL,
  partial_submission int(11) DEFAULT NULL,
  PRIMARY KEY (user_id,round_id,task_id),
  KEY round_id (round_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

alter table ia_file
  add aws tinyint(1) default 0 after remote_ip_info;

alter table ia_job
  change status status enum('waiting','done','processing','skipped') not null default 'waiting',
  add key by_task_user (task_id,user_id);

alter table ia_round
  change type type enum('classic','archive','user-defined','penalty-round','acm-round') default null;

DROP TABLE IF EXISTS ia_score_task_top_users;
CREATE TABLE ia_score_task_top_users (
  task_id varchar(64) NOT NULL,
  round_id varchar(64) NOT NULL,
  user_id int(10) unsigned NOT NULL,
  criteria varchar(64) NOT NULL,
  special_score int(11) NOT NULL,
  submit_time datetime NOT NULL,
  job_id int(10) unsigned NOT NULL,
  PRIMARY KEY (task_id,round_id,user_id,criteria)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

alter table ia_score_user_round_task
  change submits submits int(11) not null default 0,
  add incorrect_submits int(11) not null default 0 after submits,
  add KEY task_score (task_id,score) using BTREE,
  add KEY user_task_score (user_id,task_id,score) using BTREE;

alter table ia_task
  change type type enum('classic','interactive','output-only') default null,
  add solved_by int(10) unsigned default '0' after rating,
  add key by_solved (solved_by,id);

DROP TABLE IF EXISTS ia_task_users_solved;
CREATE TABLE ia_task_users_solved (
  task_id varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  user_id int(10) unsigned NOT NULL,
  PRIMARY KEY (task_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ia_task_view_sources;
CREATE TABLE ia_task_view_sources (
  user_id int(11) NOT NULL,
  task_id varchar(64) NOT NULL,
  first_request datetime NOT NULL,
  PRIMARY KEY (user_id,task_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

alter table ia_textblock
  change text text longtext DEFAULT NULL;

alter table ia_textblock_revision
  change text text longtext DEFAULT NULL;
