<?php

class Maestrano_Connec_Model_PaymentObserver
{
    /**
     * This event is dispatched every time a payment is made
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderPaymentSaveAfter(Varien_Event_Observer $observer) {
        $payment = $observer->getEvent()->getPayment();
        
        // Process payment only if related order has been pushed to Connec!
        $order = $payment->getOrder();
        if(!$order->getId()) { return; }
        $mnoIdMapModel = Mage::getModel('connec/mnoidmap');
        $orderMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($order->getId(), Mage::helper('mnomap/salesorders')->getLocalResourceName());
        if(!$orderMnoIdMap) { return; }

        // Process this object?
        if ($payment->getObserververLock() == true) {
            Mage::log('## Maestrano_Connec_Model_PaymentObserver::salesOrderPaymentSaveAfter: skipped payment '. $payment->getId());
            return;
        }

        Mage::log('## Maestrano_Connec_Model_PaymentObserver::salesOrderPaymentSaveAfter: payment ' . $payment->getId());

        // Lock the next observers for this object
        $payment->setObserververLock(true);

        /** @var Maestrano_Connec_Helper_payments $mapper */
        $mapper = Mage::helper('mnomap/payments');
        $mapper->processLocalUpdate($payment);
    }
}
