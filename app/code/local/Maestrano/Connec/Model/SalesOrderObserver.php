<?php

class Maestrano_Connec_Model_SalesOrderObserver
{
    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        // array(’item’ ⇒ $item)
        Mage::log('## cataloginventoryStockItemSaveCommitAfter: ');// . $observer->getEvent()->getItem()->debug());
    }

}
