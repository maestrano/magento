<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('admin/user'), 'mno_uid', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable' => true,
    'default' => null,
    'comment' => 'Maestrano Sso Id'
));

$installer->endSetup();

