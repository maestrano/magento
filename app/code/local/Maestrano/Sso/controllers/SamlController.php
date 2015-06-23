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
        echo "Consume action ;)";
    }

    public function MetadataAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Maestrano::toMetadata());
    }
}