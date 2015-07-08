<?php

class Maestrano_Connec_Model_CustomersObserver
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerSaveAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        /** @var Maestrano_Connec_Helper_Observerlockhelper $locker */
        $locker = Mage::helper('mnomap/observerlockhelper');
        if ($locker->isLockedGlobally()) {
            Mage::log("## Maestrano_Connec_Model_CustomerAddressesObserver::customerAddressSaveAfter - Observers are locked globally");
            return;
        }

        // Save customer in connec!
        Mage::log('## Maestrano_Connec_Model_CustomersObserver::customerSaveAfter: processing customer: ' . $customer->getId());
        /** @var Maestrano_Connec_Helper_Customers $mapper */
        $mapper = Mage::helper('mnomap/customers');
        $mapper->processLocalUpdate($customer);

        Mage::register('customer_save_observer_executed_'.$customer->getId(), true);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerDeleteAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        /** @var Maestrano_Connec_Helper_Observerlockhelper $locker */
        $locker = Mage::helper('mnomap/observerlockhelper');
        if ($locker->isLockedGlobally()) {
            Mage::log("## Maestrano_Connec_Model_CustomerAddressesObserver::customerAddressSaveAfter - Observers are locked globally");
            return;
        }

        // Delete customer in connec_mnomapid!
        Mage::log('## Maestrano_Connec_Model_CustomersObserver::customerDeleteAfter: deleting customer ' . $customer->getId());
        /** @var Maestrano_Connec_Helper_Customers $customerMapper */
        $customerMapper = Mage::helper('mnomap/customers');
        $customerMapper->processLocalUpdate($customer, false, true);
    }
}
