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

        // Save customer in connec!
        Mage::log('## Maestrano_Connec_Model_CustomerAddressesObserver::customerAddressSaveAfter: processing address: ' . $address->getId());
        /** @var Maestrano_Connec_Helper_Customeraddresses $customerMapper */
        $mapper = Mage::helper('mnomap/customeraddresses');
        $mapper->processLocalUpdate($address);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerDeleteAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getProduct();

        /** @var Maestrano_Connec_Helper_Observerlockhelper $locker */
        $locker = Mage::helper('mnomap/observerlockhelper');
        if ($locker->isLockedGlobally()) {
            Mage::log("## Maestrano_Connec_Model_CustomerAddressesObserver::customerDeleteAfter - Observers are locked globally");
            return;
        }

        // Delete customer in connec_mnomapid!
        Mage::log('## Maestrano_Connec_Model_CustomersObserver::customerDeleteAfter: deleting customer ' . $customer->getId());
        /** @var Maestrano_Connec_Helper_Customers $customerMapper */
        $customerMapper = Mage::helper('mnomap/customers');
        $customerMapper->processLocalUpdate($customer, false, true);
    }
}
