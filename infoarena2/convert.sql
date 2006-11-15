ALTER TABLE `ia_task` 
    ADD `title` VARCHAR( 64 ) NOT NULL ,
    ADD `page_name` VARCHAR( 64 ) NOT NULL ;

ALTER TABLE `ia_round` 
    ADD `title` VARCHAR( 64 ) NOT NULL ,
    ADD `page_name` VARCHAR( 64 ) NOT NULL ;

ALTER TABLE `ia_textblock` 
    ADD `security` VARCHAR( 64 ) NOT NULL ;

ALTER TABLE `ia_textblock_revision` 
    ADD `security` VARCHAR( 64 ) NOT NULL ;

UPDATE `ia_task` SET `page_name` = CONCAT('task/', `id`);
UPDATE `ia_task` 
        LEFT JOIN `ia_textblock` ON `ia_textblock`.`name` = CONCAT('task/', `ia_task`.`id`)
        SET `ia_task`.`title` = `ia_textblock`.`title`;

UPDATE `ia_round` SET `page_name` = CONCAT('round/', `id`);
UPDATE `ia_round` 
        LEFT JOIN `ia_textblock` ON `ia_textblock`.`name` = CONCAT('round/', `ia_round`.`id`)
        SET `ia_round`.`title` = `ia_textblock`.`title`;

UPDATE `ia_textblock` SET `ia_textblock`.`security` = 'public';

UPDATE `ia_textblock` 
        JOIN `ia_task` ON `ia_textblock`.`name` = `ia_task`.`page_name`
        SET `ia_textblock`.`security` = CONCAT('task: ', `ia_task`.`id`);

UPDATE `ia_textblock` 
        JOIN `ia_round` ON `ia_textblock`.`name` = `ia_round`.`page_name`
        SET `ia_textblock`.`security` = CONCAT('round: ', `ia_round`.`id`);

