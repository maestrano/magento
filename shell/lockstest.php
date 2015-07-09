<?php
require_once 'abstract.php';

class Maestrano_Shell_Locktest extends Mage_Shell_Abstract
{
    public function __construct() {
        parent::__construct();

        // Time limit to infinity
        set_time_limit(0);

        Maestrano::configure($this->_getRootPath() . "maestrano.json");
    }

    // Shell script point of entry
    public function run() {

        try {
            /** @var Maestrano_Connec_Helper_Observerlockhelper $locker */
            $locker = Mage::helper('mnomap/observerlockhelper');

            Maestrano_Shell_Locktest::log("Is locked entity 8bd60fb1-0785-0133-2f7a-22000aac0203 ? " . $locker->isLockedEntity('8bd60fb1-0785-0133-2f7a-22000aac0203'));
            $locker->lockEntity('8bd60fb1-0785-0133-2f7a-22000aac0203');
            Maestrano_Shell_Locktest::log("Is locked entity 8bd60fb1-0785-0133-2f7a-22000aac0203 ? " . $locker->isLockedEntity('8bd60fb1-0785-0133-2f7a-22000aac0203'));
            $locker->unlockEntity('8bd60fb1-0785-0133-2f7a-22000aac0203');
            Maestrano_Shell_Locktest::log("Is locked entity 8bd60fb1-0785-0133-2f7a-22000aac0203 ? " . $locker->isLockedEntity('8bd60fb1-0785-0133-2f7a-22000aac0203'));
            $locker->unlockEntity('8bd60fb1-0785-0133-2f7a-22000aac0203');

            Maestrano_Shell_Locktest::log("Is locked globally ? " . $locker->isLockedGlobally());
            $locker->lockGlobally();
            Maestrano_Shell_Locktest::log("Is locked globally ? " . $locker->isLockedGlobally());
            $locker->unlockGlobally();
        } catch (Exception $ex) {
            Maestrano_Shell_Initialize::log("### An exception occured :(");
            Maestrano_Shell_Initialize::log("### Exception: $ex->getMessage(), stacktrace: $ex->getTrace()");
        }
    }

    public static function log($text) {
        echo $text . PHP_EOL;
        Mage::log($text);
    }

    // Usage instructions
    public function usageHelp() {
        return <<<USAGE
Usage:  php -f locktest.php -- [options]
  help: This help
USAGE;
    }
}
// Instantiate
$shell = new Maestrano_Shell_Locktest();

// Initiate script
$shell->run();
