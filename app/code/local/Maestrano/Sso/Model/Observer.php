<?php

class Maestrano_Sso_Model_Observer
{
    function __construct()
    {
        Maestrano::configure('maestrano.json');
    }

    public function actionPreDispatchAdmin(Varien_Event_Observer $observer)
    {
        //Mage::dispatchEvent('admin_session_user_login_success', array('user'=>$user));
        //$user = $observer->getEvent()->getUser();
        //$user->doSomething();

        // Hook Maestrano
        if(Maestrano::sso()->isSsoEnabled()) {
            $mnoSession = new Maestrano_Sso_Session($_SESSION);
            // Check session validity and trigger SSO if not
            if (!$mnoSession->isValid()) {
                header('Location: ' . Maestrano::sso()->getInitPath());
                exit;
            }
        }
    }
}
