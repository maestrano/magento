<?php

class Maestrano_Connec_Model_CustomersObserver
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerSaveAfter(Varien_Event_Observer $observer)
    {
        //$customer = $observer->getCustomer();
        Mage::log('## customerSaveAfter: ');// . $observer->debug()); . $observer->debug());
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerDeleteAfter(Varien_Event_Observer $observer)
    {
        //$customer = $observer->getCustomer();
        Mage::log('## customerDeleteAfter: ');// . $observer->debug()); . $observer->debug());
    }
}
