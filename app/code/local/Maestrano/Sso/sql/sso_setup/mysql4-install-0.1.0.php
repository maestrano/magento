<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('admin/user'), 'mno_user', array(
    'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'nullable' => true,
    'default' => null,
    'comment' => 'Maestrano Sso Id'
));

$installer->endSetup();