<?php

class Maestrano_Connec_Model_InventoryObserver
{
    /**
     * Gets triggered on sale
     * @param Varien_Event_Observer $observer
     */
    public function cataloginventoryStockItemSaveAfter(Varien_Event_Observer $observer)
    {
        // array(’item’ ⇒ $item)
        Mage::log('## cataloginventoryStockItemSaveCommitAfter: ');// . $observer->getEvent()->getItem()->debug());
    }

    public function cataloginventoryStockItemDeleteCommitAfter(Varien_Event_Observer $observer)
    {
        // array(’item’ ⇒ $item)
        Mage::log('## cataloginventoryStockItemDeleteCommitAfter: ');// . $observer->getEvent()->getItem()->debug());
    }

    /**
     * Dispatched when an order is placed. As parameter the order placed, the products ordered and qty changes
     * @param Varien_Event_Observer $observer
     */
    public function checkoutSubmitAllAfter(Varien_Event_Observer $observer)
    {
        // array(’item’ ⇒ $item)
        Mage::log('## checkoutSubmitAllAfter: ');// . $observer->getEvent()->getItem()->debug());
    }
}
