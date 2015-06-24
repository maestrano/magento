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

            // Get the meastrano session
            $mnoSession = Mage::getSingleton('core/session')->getMnoSession();

            if ($mnoSession == null)
                echo "Session is null !!";
            else
                print_r($mnoSession);

            // Check session is present and valid, then trigger SSO if not
            if ($mnoSession == null || !$mnoSession->isValid()) {
                // The session may have bee updated while validation checking
                if ($mnoSession != null) {
                    Mage::getSingleton('core/session')->setMnoSession($mnoSession);
                }

                // Call the init action which will call the SSO server
                header('Location: ' . Maestrano::sso()->getInitPath());
                exit;
            }
        }
    }
}
