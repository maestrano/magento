<?php
require_once 'abstract.php';

class Maestrano_Shell_Initialize extends Mage_Shell_Abstract
{
    public function __construct() {
        parent::__construct();

        // Time limit to infinity
        set_time_limit(0);

        Maestrano::configure($this->_getRootPath() . "maestrano.json");
    }

    // Shell script point of entry
    public function run() {
        if(!Maestrano::param('connec.enabled')) {
            Maestrano_Shell_Initialize::log("Connec! is not enabled.");
            return false;
        }

        try {
            $filepath = $this->_getRootPath() . 'var/_data_sequence';
            $status = false;

            // Last update timestamp
            $timestamp = trim($this->openAndReadFile($filepath));
            $current_timestamp = round(microtime(true) * 1000);
            if (empty($timestamp)) { $timestamp = 0; }

            // Fetch updates
            Maestrano_Shell_Initialize::log('Fetch connec updates since ' . $timestamp);
            Maestrano_Shell_Initialize::log("URL used: updates/$timestamp?\$filter[entity]=Item");
            $client = new Maestrano_Connec_Client();
            $msg = $client->get("updates/$timestamp?\$filter[entity]=Item");
            $code = $msg['code'];
            $body = $msg['body'];

            if($code != 200) {
                Maestrano_Shell_Initialize::log("Cannot fetch connec updates code=$code, body=$body", Zend_Log::ERR);
            } else {
                Maestrano_Shell_Initialize::log("Receive updates body=$body");
                $result = json_decode($body, true);

                // Array of mapper to be executed
                $mappers[] = Mage::helper('mnomap/products');

                // Dynamically find mappers and map entities
                foreach($mappers as $mapper) {
                    Maestrano_Shell_Initialize::log("Processing mapper: " . get_class($mapper) . " with " . print_r($result[$mapper->getConnecResourceName()], 1));
                    $mapper->persistAll($result[$mapper->getConnecResourceName()], true);
                }

                $status = true;
            }

            // Set update timestamp
            if ($status) {
                file_put_contents($filepath, $current_timestamp);
            }
        } catch (Exception $ex) {
            Maestrano_Shell_Initialize::log("### An exception occured :(");
            Maestrano_Shell_Initialize::log("### Exception: $ex->getMessage(), stacktrace: $ex->getTrace()");
        }
    }

    public static function log($text) {
        echo $text . PHP_EOL;
        Mage::log($text);
    }

    // Utility method to open and create file if necessary
    private function openAndReadFile($file_path) {
        if(!file_exists($file_path)) {
            $fp = fopen($file_path, "w");
            fwrite($fp,"");
            fclose($fp);
        }
        return file_get_contents($file_path);
    }

    // Usage instructions
    public function usageHelp() {
        return <<<USAGE
Usage:  php -f initialize.php -- [options]
  help: This help
USAGE;
    }
}
// Instantiate
$shell = new Maestrano_Shell_Initialize();

// Initiate script
$shell->run();
