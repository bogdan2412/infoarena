DELIMITER //

DROP PROCEDURE IF EXISTS convert_round //
CREATE PROCEDURE convert_round(test_run BOOLEAN) BEGIN

    -- Add allow_eval to ia_round
    DROP TABLE IF EXISTS ia_round_new;
    CREATE TABLE `ia_round_new` (
        `id` varchar(64) collate latin1_general_ci NOT NULL default '',
        `title` varchar(64) collate latin1_general_ci default NULL,
        `page_name` varchar(64) collate latin1_general_ci default NULL,
        `state` enum('running','waiting','complete') collate latin1_general_ci NOT NULL default 'waiting',
        `type` enum('classic','archive') collate latin1_general_ci NOT NULL,
        `start_time` datetime default NULL,
        `end_time` datetime default NULL,
        `rating_timestamp` datetime DEFAULT NULL,
        `allow_eval` BOOLEAN NOT NULL,
        `allow_submit` BOOLEAN NOT NULL,
        `affects_rating` BOOLEAN NOT NULL,
        PRIMARY KEY  (`id`)
    );

    -- Copy data.
    INSERT INTO `ia_round_new` (`id`, `title`, `page_name`,
            `state`, `type`, `start_time`,
            `end_time`, `rating_timestamp`, `allow_eval`, `allow_submit`, `affects_rating`)
        SELECT `id`, `title`, `page_name`, `state`, `type`,`start_time`,
            DATE_ADD(`round`.`start_time`, INTERVAL 
                (SELECT `param`.`value` FROM `ia_parameter_value` AS `param`
                    WHERE `param`.`object_type` = 'round'
                        AND `param`.`object_id` = `round`.`id`
                        AND `param`.`parameter_id` = 'duration'
                    LIMIT 1) HOUR) AS `end_time`,
            (SELECT FROM_UNIXTIME(`param`.`value`) FROM `ia_parameter_value` AS `param`
                WHERE `param`.`object_type` = 'round'
                    AND `param`.`object_id` = `round`.`id`
                    AND `param`.`parameter_id` = 'rating_timestamp'
                LIMIT 1) AS `rating_timestamp`,
            TRUE as `allow_eval`,
            `id` = 'arhiva' AS `allow_submit`,
            (SELECT `param`.`value` FROM `ia_parameter_value` AS `param`
                WHERE `param`.`object_type` = 'round'
                    AND `param`.`object_id` = `round`.`id`
                    AND `param`.`parameter_id` = 'rating_update'
                LIMIT 1) AS `affects_rating`
        FROM `ia_round` AS `round`;

    -- Replace old table, delete from ia_parameter_value
    IF NOT test_run THEN
        DELETE FROM `ia_parameter_value`
            WHERE `object_type` = 'round';
        DROP TABLE `ia_round`;
        RENAME TABLE `ia_round_new` TO `ia_round`;
    END IF;
END //

DROP PROCEDURE IF EXISTS convert_job //
CREATE PROCEDURE convert_job(test_run BOOLEAN) BEGIN
    -- Create new job table
    DROP TABLE IF EXISTS ia_job_new;
    CREATE TABLE `ia_job_new` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
        `user_id` INT UNSIGNED NOT NULL,
        -- Allow nulls at first.
        `round_id` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_general_ci NULL,
        `task_id` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
        `submit_time` DATETIME NOT NULL ,
        `compiler_id` VARCHAR(8) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
        `file_contents` LONGBLOB NOT NULL ,
        `status` ENUM('waiting', 'done', 'processing') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'waiting',
        `eval_log` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
        `score` INT(11) NULL DEFAULT NULL ,
        `eval_message` VARCHAR(256) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    );

    INSERT INTO `ia_job_new` (
            `user_id`, `task_id`, `round_id`,
            `submit_time`, `compiler_id`, `file_contents`,
            `status`, `eval_log`, `score`, `eval_message`)
        SELECT `user_id`, `job`.`task_id`, `round`.`id` as `round_id`,
                `submit_time`, `compiler_id`, `file_contents`,
                `status`, `eval_log`, `score`, `eval_message`
            FROM `ia_job` AS `job`
            LEFT JOIN `ia_round_task` AS `round_task`
                ON `round_task`.`task_id` = `job`.`task_id`
                AND `round_task`.`round_id` != 'arhiva'
            -- Left join. jobs with no contest get a null.
            LEFT JOIN `ia_round` AS `round` 
                ON `round`.`id` = `round_task`.`round_id`
                AND `round`.`type` = 'classic'
                AND `job`.`submit_time` > `round`.`start_time`
                AND `job`.`submit_time` < `round`.`end_time`
            ORDER BY `submit_time` ASC;

    -- Assign jobs with null round_id to archive.                    
    UPDATE `ia_job_new`
        SET `round_id` = 'arhiva'
        WHERE `round_id` IS NULL;

    -- Remove nullability from round_id
    ALTER TABLE `ia_job_new`
        CHANGE `round_id` `round_id` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
        ADD KEY `by_time` (`submit_time`),
        ADD KEY `by_round` (`round_id`, `submit_time`),
        ADD KEY `by_task` (`task_id`, `submit_time`),
        ADD KEY `by_round_task` (`round_id`, `task_id`, `submit_time`),
        ADD KEY `by_user` (`user_id`, `submit_time`);

    IF NOT test_run THEN
        -- Replace job table.
        DROP TABLE `ia_job`;
        RENAME TABLE `ia_job_new` TO `ia_job`;
    END IF;
END //

DROP PROCEDURE IF EXISTS convert_score //
CREATE PROCEDURE convert_score(test_run BOOLEAN) BEGIN
    -- Create new scores table.
    DROP TABLE IF EXISTS `ia_user_score`;
    CREATE TABLE `ia_user_score` (
            `user_id` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`user_id`),
            `rating` INT NULL,
            `rating_timestamp` INT NULL
    );

    INSERT INTO `ia_user_score` (`user_id`, `rating`, `rating_timestamp`)
        SELECT `user`.`id` AS `user_id`, 
               `user`.`rating_cache` AS `rating`,
               (SELECT `score` FROM `ia_score` AS `score`
                    WHERE `score`.`user_id` = `user`.`id`
                      AND `score`.`task_id` IS NULL
                      AND `score`.`round_id` IS NULL
                      AND `score`.`name` = 'rating_timestamp') AS `rating_timestamp`
            FROM `ia_user` AS `user`;


    DROP TABLE IF EXISTS `ia_user_round_score`;
    CREATE TABLE `ia_user_round_score` (
            `user_id` INT UNSIGNED NOT NULL,
            `round_id` VARCHAR(64) NOT NULL,
            `score` INT NULL,
            `rating` INT NULL,
            `rating_deviation` INT NULL,
            PRIMARY KEY (`user_id`, `round_id`)
    );

    INSERT INTO `ia_user_round_score` (`user_id`, `round_id`, `rating`, `rating_deviation`, `score`)
        SELECT `score`.`user_id` AS `user_id`,
               `score`.`round_id` AS `round_id`,
               `score`.`score` AS `rating`,
               (SELECT `score` FROM `ia_score` AS `_score`
                    WHERE `_score`.`user_id` = `score`.`user_id`
                      AND `_score`.`round_id` = `score`.`round_id`
                      AND `_score`.`task_id` IS NULL
                      AND `_score`.`name` = 'deviation'
                      LIMIT 1) AS `rating_deviation`,
               (SELECT SUM(`score`) FROM `ia_score` AS `_score`
                    WHERE `_score`.`user_id` = `score`.`user_id`
                      AND `_score`.`round_id` = `score`.`round_id`
                      AND `_score`.`name` = 'score') AS `score`
            FROM `ia_score` AS `score`
                WHERE `score`.`name` = 'rating';


    DROP TABLE IF EXISTS `ia_user_round_task_score`;
    CREATE TABLE `ia_user_round_task_score` (
            `user_id` INT UNSIGNED NOT NULL,
            `round_id` VARCHAR(64) NOT NULL,
            `task_id` VARCHAR(64) NOT NULL,
            PRIMARY KEY (`user_id`, `round_id`, `task_id`),
            `score` INT NULL,
            `submit_count` INT NULL,
            `job_id` INT NULL
    );

    INSERT INTO `ia_user_round_task_score` (
            `user_id`, `round_id`, `task_id`,
            `score`, `submit_count`, `job_id`)
        SELECT `score`.`user_id` AS `user_id`,
               `score`.`round_id` AS `round_id`,
               `score`.`score` AS `rating`,
               (SELECT `score` FROM `ia_score` AS `_score`
                    WHERE `_score`.`user_id` = `score`.`user_id`
                      AND `_score`.`round_id` = `score`.`round_id`
                      AND `_score`.`task_id` = `score`.`task_id`
                      AND `_score`.`name` = 'submit_count') AS `submit_count`
               /*(SELECT `id` FROM `ia_job` AS `job`
                    WHERE `job`.`user_id` = `score`.`user_id`
                      AND `job`.`task_id` = `score`.`task_id`
                      AND `job`.`round_id` = `score`.`round_id`
                    ORDER BY `submit_time` DESC
                    LIMIT 1) AS `job_id`*/
            FROM `ia_score` AS `score`
                WHERE `score`.`name` = 'score';

    IF NOT test_run THEN
        -- Replace scores table.
        DROP TABLE IF EXISTS `ia_score`;
    END IF;
END //

--CALL convert_round(0) //
--CALL convert_job(0) //
--CALL convert_score(1) //

DROP PROCEDURE convert_round //
DROP PROCEDURE convert_job //
DROP PROCEDURE convert_score //
