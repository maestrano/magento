<?php

class Maestrano_Connec_Model_SalesOrderObserver
{

    /**
     * This event is dispatched after the order payment is placed from the function Mage_Sales_Model_Order::place()
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        Mage::log('## Maestrano_Connec_Model_SalesOrderObserver::salesOrderPlaceAfter: order ' . $order->getId());

        /** @var Maestrano_Connec_Helper_Salesorders $mapper */
        $mapper = Mage::helper('mnomap/salesorders');
        $mapper->processLocalUpdate($order);
    }

}
