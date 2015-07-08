<?php

class Maestrano_Connec_DataController extends Mage_Core_Controller_Front_Action
{
    function preDispatch()
    {
        Maestrano::configure('maestrano.json');
    }

    public function SuscribeAction()
    {
        if(!Maestrano::param('connec.enabled')) { return false; }

        $notification = json_decode(file_get_contents('php://input'), false);
        $entity_name = strtoupper(trim($notification->entity));
        $entity_id = $notification->id;

        Mage::log("Maestrano_Connec_DataController::SuscribeAction - received notification: " . json_encode($notification));

        switch ($entity_name) {
            case "ITEMS":
                /** @var Maestrano_Connec_Helper_Products $helper */
                $helper = Mage::helper('mnomap/products');
                $helper->fetchConnecResource($entity_id);
                break;
        }

        $this->getResponse()->setBody("Fetched $entity_name $entity_id");
    }
}