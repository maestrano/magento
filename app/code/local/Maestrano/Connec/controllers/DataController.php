<?php

class Maestrano_Connec_DataController extends Mage_Core_Controller_Front_Action
{
    function preDispatch()
    {
        Maestrano::configure('maestrano.json');
    }

    public function SubscribeAction()
    {
        if(!Maestrano::param('connec.enabled')) { return false; }

        /** @var Maestrano_Connec_Helper_Observerlockhelper $locker */
        $locker = Mage::helper('mnomap/observerlockhelper');

        try
        {
            $notification = json_decode(file_get_contents('php://input'), false);
            $entity_name = strtoupper(trim($notification->entity));
            $entity_id = $notification->id;

            $locker->lockGlobally();

            Mage::log("Maestrano_Connec_DataController::SuscribeAction - received notification: " . json_encode($notification));

            switch ($entity_name) {
                case "ITEMS":
                    /** @var Maestrano_Connec_Helper_Products $helper */
                    $helper = Mage::helper('mnomap/products');
                    $helper->fetchConnecResource($entity_id);
                    break;
                case "PERSONS":
                    /** @var Maestrano_Connec_Helper_Customers $helper */
                    $helper = Mage::helper('mnomap/customers');
                    $helper->fetchConnecResource($entity_id);
                    break;
            }

            $this->getResponse()->setBody("Fetched $entity_name $entity_id");
        } catch (Exception $ex) {
            $this->getResponse()->setBody("An error occured: " . $ex->getMessage() . "\nStacktrace: " . $ex->getTrace());
        } finally {
            $locker->unlockGlobally();
        }
    }
}