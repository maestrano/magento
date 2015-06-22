<?php

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__FILE__) . '/../'); }
chdir(ROOT_PATH);

// Include Maestrano required libraries
require_once(ROOT_PATH . '/lib/Maestrano.php');

Maestrano::configure(ROOT_PATH . 'maestrano.json');
