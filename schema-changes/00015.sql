alter table cookie charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_file charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_job charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_job_test charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_parameter_value charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_rating charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_round charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_round_tags charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_round_task charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_score_user_round charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_score_user_round_task charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_task charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_task_tags charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_textblock charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_textblock_revision charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_textblock_tags charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_user charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_user_round charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_user_tags charset utf8mb4 collate utf8mb4_romanian_ci;
alter table ia_variable charset utf8mb4 collate utf8mb4_romanian_ci;

alter table cookie modify string varchar(50) not null default '';

alter table ia_file modify name varchar(64) not null;
alter table ia_file modify page varchar(64) not null;
alter table ia_file modify mime_type varchar(64) not null;
alter table ia_file modify remote_ip_info varchar(128) not null;

alter table ia_job modify round_id varchar(64) not null;
alter table ia_job modify task_id varchar(64) not null;
alter table ia_job modify compiler_id varchar(8) not null;
alter table ia_job modify status enum('waiting','done','processing','skipped') not null default 'waiting';
update ia_job set eval_message = '' where eval_message is null;
alter table ia_job modify eval_message varchar(256) not null default '';
alter table ia_job modify remote_ip_info varchar(128) not null;

alter table ia_job_test modify grader_message varchar(128) not null default '';

alter table ia_parameter_value modify parameter_id varchar(64) not null default '';
alter table ia_parameter_value modify object_type enum('global','round','task') not null default 'global';
alter table ia_parameter_value modify object_id varchar(64) not null default '';
alter table ia_parameter_value modify value varchar(256) not null default '';

alter table ia_rating modify round_id varchar(64) not null default '';

alter table ia_round modify id varchar(64) not null default '';
alter table ia_round modify title varchar(64) not null default '';
alter table ia_round modify page_name varchar(64) not null default '';
alter table ia_round modify state enum('running','waiting','complete') not null default 'waiting';
alter table ia_round modify type enum('classic','archive','user-defined','penalty-round','acm-round') not null default 'user-defined';

alter table ia_round_tags modify round_id varchar(64) not null default '';

alter table ia_round_task modify round_id varchar(64) not null default '';
alter table ia_round_task modify task_id varchar(64) not null default '';

alter table ia_score_user_round modify round_id varchar(64) not null default '';

alter table ia_score_user_round_task modify round_id varchar(64) not null default '';
alter table ia_score_user_round_task modify task_id varchar(64) not null default '';

alter table ia_task modify id varchar(64) not null default '';
alter table ia_task modify source varchar(64) not null default '';
alter table ia_task modify security enum('private','protected','public') not null default 'private';
alter table ia_task modify title varchar(64) not null default '';
alter table ia_task modify page_name varchar(64) not null default '';
alter table ia_task modify type enum('classic','interactive','output-only') not null default 'classic';
alter table ia_task modify test_groups varchar(256) not null default '';
alter table ia_task modify public_tests varchar(256) not null default '';
alter table ia_task modify evaluator varchar(64) not null default '';

alter table ia_task_tags modify task_id varchar(64) not null default '';

alter table ia_textblock modify name varchar(64) not null default '';
alter table ia_textblock modify title varchar(64) not null default '';
alter table ia_textblock modify text longtext not null default '';
alter table ia_textblock modify security varchar(64) not null default '';
alter table ia_textblock modify remote_ip_info varchar(128) not null default '';

alter table ia_textblock_revision modify name varchar(64) not null default '';
alter table ia_textblock_revision modify title varchar(64) not null default '';
alter table ia_textblock_revision modify text longtext not null default '';
alter table ia_textblock_revision modify security varchar(64) not null default '';
update ia_textblock_revision set remote_ip_info = '' where remote_ip_info is null;
alter table ia_textblock_revision modify remote_ip_info varchar(128) not null default '';

alter table ia_textblock_tags modify textblock_id varchar(64) not null default '';

alter table ia_user modify username varchar(64) not null default '';
alter table ia_user modify password varchar(64) not null default '';
alter table ia_user modify email varchar(64) not null default '';
alter table ia_user modify full_name varchar(64) not null default '';
alter table ia_user modify security_level enum('admin','helper','intern','normal') not null default 'normal';

alter table ia_user_round modify round_id varchar(64) charset utf8mb4 collate utf8mb4_romanian_ci;

alter table ia_variable modify name varchar(100) not null default '';
alter table ia_variable modify value varchar(100) not null default '';
