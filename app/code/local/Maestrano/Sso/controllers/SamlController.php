<?php

class Maestrano_Sso_SamlController extends Mage_Core_Controller_Front_Action
{
    function preDispatch()
    {
        Maestrano::configure('maestrano.json');
    }

    public function InitAction()
    {
        // Create a Saml request and redirect to the server
        $req = new Maestrano_Saml_Request($_GET);
        $this->_redirectUrl($req->getRedirectUrl());
    }

    public function ConsumeAction()
    {
        try {
            $resp = new Maestrano_Saml_Response($_POST['SAMLResponse']);
            Mage::log("## Maestrano_Sso_SamlController->ConsumeAction - is response valid: " . $resp->isValid());

            // Check if the Saml response is valid
            if ($resp->isValid()) {
                // Get the user as well as the user group
                $mnoUser = new Maestrano_Sso_User($resp);

                // Create a new maestrano session and save it in magento session
                $mnoSession = new Maestrano_Sso_Session($_SESSION, $mnoUser);
                Mage::getSingleton('admin/session')->setMnoSession($mnoSession);

                Mage::log("## Maestrano_Sso_SamlController->ConsumeAction - Saved session (UID): " . $mnoSession->getUid());

                // Find user in db by uid or email
                $userModel = Mage::getModel('admin/user');
                $userDB = $userModel->findOrCreate($mnoUser);

                // Convert array to Maestrano_Sso_Model_User
                // May be a better way to do it...
                $userObject = Mage::getModel('admin/user')->loadByUsername($userDB['username']);

                Mage::log("## Maestrano_Sso_SamlController->ConsumeAction - User from db: " . $userObject->getUsername());

                // Load the user in the magento session
                $session = Mage::getSingleton('admin/session');
                $session->setIsFirstVisit(true);
                $session->setUser($userObject);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());

                Mage::dispatchEvent('admin_session_user_login_success', array('user' => $userObject));

                // If logged in redirect to admin dashboard startup page
                if ($session->isLoggedIn()) {
                    Mage::log("## Maestrano_Sso_SamlController->ConsumeAction - User is logged in!");
                    echo $redirectUrl = Mage::getSingleton('adminhtml/url')->getUrl(Mage::getModel('admin/user')->getStartupPageUrl(), array('_current' => false));
                    Mage::log("## Maestrano_Sso_SamlController->ConsumeAction - Redirecting to: " . $redirectUrl);
                    header('Location: ' . $redirectUrl);
                    exit;
                }
            } else {
                echo '<p>There was an error during the authentication process.</p><br/>';
                echo '<p>Please try again. If issue persists please contact support@maestrano.com<p>';
            }
        } catch (Exception $ex) {
            Mage::logException($ex);
            echo $ex;
        }
    }

    public function MetadataAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Maestrano::toMetadata());
    }
}
