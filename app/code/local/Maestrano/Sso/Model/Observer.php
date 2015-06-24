<?php

class Maestrano_Sso_Model_Observer
{
    function __construct()
    {
        Maestrano::configure('maestrano.json');
    }

    public function actionPreDispatchAdmin(Varien_Event_Observer $observer)
    {
        // Hook Maestrano
        if (Maestrano::sso()->isSsoEnabled()) {
            Mage::log("## Maestrano_Sso_Model_Observer - SSO is enabled");

            // Get the meastrano session
            $mnoSession = Mage::getSingleton('admin/session')->getMnoSession();

            if ($mnoSession == null)
                Mage::log("## Maestrano_Sso_Model_Observer - Session is null!!");

            // Check session is present and valid, then trigger SSO if not
            if ($mnoSession == null || !$mnoSession->isValid()) {
                Mage::log("## Maestrano_Sso_Model_Observer - Session is not valid.");

                // The session may have bee updated while validation checking
                Mage::getSingleton('admin/session')->setMnoSession($mnoSession);

                // Call the init action which will call the SSO server
                header('Location: ' . Maestrano::sso()->getInitPath());
                exit;
            }
        }
    }
}
