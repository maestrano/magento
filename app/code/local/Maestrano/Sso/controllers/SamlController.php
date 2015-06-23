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

    public function ConsumeAction()
    {
        echo "Consume action:<br/>";
        //$resp = new Maestrano_Saml_Response($_POST['SAMLResponse']);

        $userModel = Mage::getModel('admin/user');
        $userModel->findOrCreate();

        /*
        if ($resp->isValid()) {
            // Get the user as well as the user group
            $user = new Maestrano_Sso_User($resp);
            $group = new Maestrano_Sso_Group($resp);

            echo "- User: " . $user->getEmail() . "<br/>";
            echo "- Group: " . $group->getName() . "<br/>";

            //$userModel = Mage::getModel('admin/user');
            // Find user by uid or email
            //$userDB = $userModel->findOrCreate($user);

            //echo "- UserDB: " . $userDB->getFirstname() . "<br/>";
        } else {
            echo '<p>There was an error during the authentication process.</p><br/>';
            echo '<p>Please try again. If issue persists please contact support@maestrano.com<p>';
        }*/
    }

    public function MetadataAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Maestrano::toMetadata());
    }
}
