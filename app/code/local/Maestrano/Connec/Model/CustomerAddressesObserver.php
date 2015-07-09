<?php

class Maestrano_Connec_Model_CustomerAddressesObserver
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerAddressSaveAfter(Varien_Event_Observer $observer)
    {
        $address = $observer->getEvent()->getCustomerAddress();

        /** @var Maestrano_Connec_Helper_Observerlockhelper $locker */
        $locker = Mage::helper('mnomap/observerlockhelper');
        if ($locker->isLockedGlobally()) {
            Mage::log("## Maestrano_Connec_Model_CustomerAddressesObserver::customerAddressSaveAfter - Observers are locked globally");
            return;
        }

        $observerLock = $address->getObserverLock();
        if ($observerLock) {
            Mage::log("## Maestrano_Connec_Model_CustomerAddressesObserver::customerAddressSaveAfter - Observers are locked for address " . $address->getId());
            return;
        }

        // Save customer in connec!
        Mage::log('## Maestrano_Connec_Model_CustomerAddressesObserver::customerAddressSaveAfter: processing address: ' . $address->getId());
        /** @var Maestrano_Connec_Helper_Customeraddresses $customerMapper */
        $mapper = Mage::helper('mnomap/customeraddresses');
        $mapper->processLocalUpdate($address);
    }
}
