<?php
$installer = $this;
$installer->startSetup();

$sql=<<<SQLTEXT
create TABLE `connec_mnoidmap` (
    `mnoidmap_id` int(11) unsigned not null,
    `mno_entity_guid` varchar(255) not null,
    `mno_entity_name` varchar(255) not null,
    `app_entity_id` varchar(255) not null,
    `app_entity_name` varchar(255) not null,
    `db_timestamp` timestamp not null default '0000-00-00 00:00:00',
    `deleted_flag` int(1) not null default '0',
    PRIMARY KEY (`mnoidmap_id`)
);
CREATE UNIQUE INDEX `mno_id_map_unique_key` ON `connec_mnoidmap` (`mno_entity_guid`, `mno_entity_name`, `app_entity_id`, `app_entity_name`);
CREATE INDEX mno_id_map_mno_key ON `connec_mnoidmap` (`mno_entity_guid`, `mno_entity_name`);
CREATE INDEX mno_id_map_app_key ON `connec_mnoidmap` (`app_entity_id`, `app_entity_name`);
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
