<?php

class Maestrano_Sso_SamlController extends Mage_Core_Controller_Front_Action
{
    function preDispatch()
    {
        Maestrano::configure('maestrano.json');
    }

    public function InitAction()
    {
        $req = new Maestrano_Saml_Request($_GET);
        $this->_redirectUrl($req->getRedirectUrl());
    }

    /**
     *
     */
    public function ConsumeAction()
    {
        echo "Consume action:<br/>";
        $resp = new Maestrano_Saml_Response($_POST['SAMLResponse']);

        $userModel = Mage::getModel('admin/user');

        if ($resp->isValid()) {
            // Get the user as well as the user group
            $mnoUser = new Maestrano_Sso_User($resp);
            $mnoGroup = new Maestrano_Sso_Group($resp);

            echo "- Mno User: " . print_r($mnoUser) . "<br/>";
            echo "- Mno Group: " . print_r($mnoGroup) . "<br/>";

            // Find user by uid or email
            $userDB = $userModel->findOrCreate($mnoUser);
            if ($userDB)
                echo "- User Magento: " . $userDB->getFirstname() . "<br/>";
            else
                echo "User not found" . "<br/>";
        } else {
            echo '<p>There was an error during the authentication process.</p><br/>';
            echo '<p>Please try again. If issue persists please contact support@maestrano.com<p>';
        }
    }

    public function MetadataAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Maestrano::toMetadata());
    }
}
