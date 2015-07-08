<?php

class Maestrano_Connec_Model_CustomerAddressesObserver
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerAddressSaveAfter(Varien_Event_Observer $observer)
    {
        Mage::log("## in customerAddressSaveAfter()");
        $address = $observer->getEvent()->getCustomerAddress();
        //$customer = Mage::getModel('customer/customer')->load($address->getCustomerId());

        $observerLock = $address->getObserverLock();
        if ($observerLock) {
            Mage::log("## Maestrano_Connec_Model_CustomerAddressesObserver::customerAddressSaveAfter - Observers are locked for address " . $address->getId());
            return;
        }

        // Save customer in connec!
        Mage::log('## Maestrano_Connec_Model_CustomerAddressesObserver::customerAddressSaveAfter: processing customer: ' . $address->getId());
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

        $observerLock = $customer->getObserverLock();
        if ($observerLock) {
            Mage::log("## Maestrano_Connec_Model_CustomersObserver::customerDeleteAfter - Observers are locked for customer " . $customer->getId());
            return;
        }

        // Delete customer in connec_mnomapid!
        Mage::log('## Maestrano_Connec_Model_CustomersObserver::customerDeleteAfter: deleting customer ' . $customer->getId());
        /** @var Maestrano_Connec_Helper_Customers $customerMapper */
        $customerMapper = Mage::helper('mnomap/customers');
        $customerMapper->processLocalUpdate($customer, false, true);
    }
}
