<?php
$installer = $this;
$installer->startSetup();

$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `connec_mnoidmap` (
  `mno_entity_guid` varchar(255) NOT NULL,
  `mno_entity_name` varchar(255) NOT NULL,
  `app_entity_id` varchar(255) NOT NULL,
  `app_entity_name` varchar(255) NOT NULL,
  `db_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_flag` int(1) NOT NULL DEFAULT '0'
);

CREATE UNIQUE INDEX mno_id_map_unique_key ON `connec_mnoidmap` (`mno_entity_guid`, `mno_entity_name`, `app_entity_id`, `app_entity_name`);
CREATE INDEX mno_id_map_mno_key ON `connec_mnoidmap` (`mno_entity_guid`, `mno_entity_name`);
CREATE INDEX mno_id_map_app_key ON `connec_mnoidmap` (`app_entity_id`, `app_entity_name`);
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
