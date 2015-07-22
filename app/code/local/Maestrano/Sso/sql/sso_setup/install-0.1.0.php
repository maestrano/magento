<?php
$installer = $this;
$installer->startSetup();

$sql=<<<SQLTEXT
ALTER TABLE admin_user ADD CONSTRAINT UNQ_EMAIL UNIQUE (email);
ALTER TABLE admin_user ADD mno_uid VARCHAR(10) NULL;
ALTER TABLE admin_user ADD CONSTRAINT UNQ_MNO_UID UNIQUE (mno_uid);
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
