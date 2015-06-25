<?php

class Maestrano_Sso_Model_Observer
{
    function __construct()
    {
        Mage::log("## Maestrano_Sso_Model_Observer - in __construct:");
        Maestrano::configure('maestrano.json');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function actionPreDispatchAdmin(Varien_Event_Observer $observer)
    {
        $valTest = Mage::getSingleton('core/session')->getValTest();
        Mage::log("## Maestrano_Sso_Model_Observer - valTest: " . $valTest);

        // Hook Maestrano
        Mage::log("## Maestrano_Sso_Model_Observer - SSO is enabled: " . Maestrano::sso()->isSsoEnabled());
        if (Maestrano::sso()->isSsoEnabled()) {
            // Get the meastrano session

            /**
             * @var $mnoSession Maestrano_Sso_Session
             */
            $mnoSession = Mage::getSingleton('core/session')->getMnoSession();

            if ($mnoSession == null)
                Mage::log("## Maestrano_Sso_Model_Observer - mnoSession is null!!");

            // Check session is present and valid, then trigger SSO if not
            if ($mnoSession == null || !$mnoSession->isValid()) {
                Mage::log("## Maestrano_Sso_Model_Observer - mnoSession is not valid.");
                Mage::log("## Maestrano_Sso_Model_Observer - Redirecting to " . Maestrano::sso()->getInitPath());

                // The session may have bee updated while validation checking
                Mage::getSingleton('core/session')->setMnoSession($mnoSession);

                // Call the init action which will call the SSO server
                header('Location: ' . Maestrano::sso()->getInitPath());
                exit;
            } else {
                Mage::log("## Maestrano_Sso_Model_Observer - mnoSession is valid ;)");
            }
        }
    }
}
