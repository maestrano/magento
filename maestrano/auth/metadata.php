<?php
header('Content-Type: application/json');

require_once '../_init.php';

// Authenticate using http basic
if (isset($_SERVER['PHP_AUTH_USER'])
    && isset($_SERVER['PHP_AUTH_PW'])
    && Maestrano::authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
    echo Maestrano::toMetadata();
} else {
    echo "Sorry! I'm not giving you my API metadata";
}
