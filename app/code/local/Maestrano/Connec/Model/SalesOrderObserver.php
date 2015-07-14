<?php

class Maestrano_Connec_Model_SalesOrderObserver
{
    /**
     * This event is dispatched every time the order is saved
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        // Process this object?
        if ($order->getObserververLock() == true) {
            Mage::log('## Maestrano_Connec_Model_SalesOrderObserver::salesOrderSaveAfter: skipped order '. $order->getId());
            return;
        }

        // The status need to be updated
        if (!empty($order->getStatus())) {
            Mage::log('## Maestrano_Connec_Model_SalesOrderObserver::salesOrderSaveAfter: order ' . $order->getId() . ", state: " . $order->getState() . ", status: " . $order->getStatus());

            // Lock the next observers for this object
            $order->setObserververLock(true);

            /** @var Maestrano_Connec_Helper_Salesorders $mapper */
            $mapper = Mage::helper('mnomap/salesorders');
            $mapper->processLocalUpdate($order);
        }
    }

    /**
     * This event is dispatched every time the invoice is saved
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderInvoiceSaveAfter(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getInvoice();

        // Process this object?
        if ($invoice->getObserververLock() == true) {
            Mage::log('## Maestrano_Connec_Model_SalesOrderObserver::salesOrderInvoiceSaveAfter: skipped invoice '. $invoice->getId());
            return;
        }

        // The status need to be updated
        if (!empty($invoice->getStateName())) {
            Mage::log('## Maestrano_Connec_Model_SalesOrderObserver::salesOrderInvoiceSaveAfter: invoice ' . $invoice->getId() . ", state: " . $invoice->getStateName());

            // Lock the next observers for this object
            $invoice->setObserververLock(true);

            /** @var Maestrano_Connec_Helper_Salesorders $mapper */
            $mapper = Mage::helper('mnomap/invoices');
            $mapper->processLocalUpdate($invoice);
        }
    }
}
