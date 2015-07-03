<?php

class Maestrano_Connec_Model_CustomerAddressesObserver
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerAddressSaveAfter(Varien_Event_Observer $observer)
    {
        $address = $observer->getCustomerAddress();
        //$customer= $address->getCustomer()
        Mage::log('## customerAddressSaveAfter: ' . $address->debug());
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerAddressDeleteAfter(Varien_Event_Observer $observer)
    {
        $address = $observer->getCustomerAddress();
        //$customer= $address->getCustomer()
        Mage::log('## customerAddressSaveAfter: ' . $address->debug());
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlCustomerSaveAfter(Varien_Event_Observer $observer)
    {
        $address = $observer->getCustomerAddress();
        //$customer= $address->getCustomer()
        Mage::log('## customerAddressSaveAfter: ' . $address->debug());
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlCustomerDeleteAfter(Varien_Event_Observer $observer)
    {
        $address = $observer->getCustomerAddress();
        //$customer= $address->getCustomer()
        Mage::log('## customerAddressSaveAfter: ' . $address->debug());
    }
}
