<?php
$installer = $this;
$installer->startSetup();

$sql=<<<SQLTEXT
CREATE TABLE `connec_mnoidmap` (
    `mnoidmap_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `mno_entity_guid` VARCHAR(255) NOT NULL,
    `mno_entity_name` VARCHAR(255) NOT NULL,
    `app_entity_id` VARCHAR(255) NOT NULL,
    `app_entity_name` VARCHAR(255) NOT NULL,
    `db_timestamp` TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
    `deleted_flag` int(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`mnoidmap_id`)
);
CREATE UNIQUE INDEX `mno_id_map_unique_key` ON `connec_mnoidmap` (`mno_entity_guid`, `mno_entity_name`, `app_entity_id`, `app_entity_name`);
CREATE INDEX mno_id_map_mno_key ON `connec_mnoidmap` (`mno_entity_guid`, `mno_entity_name`);
CREATE INDEX mno_id_map_app_key ON `connec_mnoidmap` (`app_entity_id`, `app_entity_name`);
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
