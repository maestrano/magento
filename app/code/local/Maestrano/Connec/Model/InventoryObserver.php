<?php

class Maestrano_Connec_Model_InventoryObserver
{
    /**
     * Is triggered when a customer place an order
     * @param Varien_Event_Observer $observer
     */
    public function subtractQuoteInventory(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            if ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'bundle' || $product->getTypeId() == 'grouped'){
                continue;
            }

            // Update those items
            $delta = ($item->getTotalQty() * -1);
            Mage::log('## Maestrano_Connec_Model_InventoryObserver::subtractQuoteInventory: updating product:' . $item->getProduct()->getId() . ', qty: ' . $item->getProduct()->getStockItem()->getQty() . ", delta: " . $delta);
            $newQty = $product->getStockItem()->getQty() + $delta;
            $product->getStockItem()->setQty($newQty);
            /** @var Maestrano_Connec_Helper_Products $helper */
            $helper = Mage::helper('mnomap/products');
            $helper->pushToConnec($product);
        }
    }

    public function revertQuoteInventory(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            if (is_null($product)) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
            }

            if ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'bundle' || $product->getTypeId() == 'grouped'){
                continue;
            }

            // Update those items
            Mage::log('## Maestrano_Connec_Model_InventoryObserver::revertQuoteInventory: updating product:' . $product->getId() . ', qty: ' . $product->getStockItem()->getQty() . ", delta: " . $item->getTotalQty());
            /** @var Maestrano_Connec_Helper_Products $helper */
            $helper = Mage::helper('mnomap/products');
            $helper->pushToConnec($product);
        }
    }

    /**
     * Is triggered when an order is canceled on admin panel
     * @param Varien_Event_Observer $observer
     */
    public function cancelOrderItem(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getItem();
        $product = $item->getProduct();
        if (is_null($product)) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }

        if ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'bundle' || $product->getTypeId() == 'grouped'){
            return;
        }

        // Update this item
        Mage::log('## Maestrano_Connec_Model_InventoryObserver::cancelOrderItem: updating product:' . $product->getId() . ', qty: ' . $product->getStockItem()->getQty());
        /** @var Maestrano_Connec_Helper_Products $helper */
        $helper = Mage::helper('mnomap/products');
        $helper->pushToConnec($product);
    }

    /**
     * Is triggered when a credit memo is created on an invoce to refund a customer
     * @param Varien_Event_Observer $observer
     */
    public function refundOrderInventory(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();

        foreach ($creditmemo->getAllItems() as $item) {
            $product = $item->getProduct();
            if (is_null($product)) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
            }

            if ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'bundle' || $product->getTypeId() == 'grouped'){
                continue;
            }

            // Update those items
            Mage::log('## Maestrano_Connec_Model_InventoryObserver::refundOrderInventory: updating product:' . $product->getId() . ', qty: ' . $product->getStockItem()->getQty());
            /** @var Maestrano_Connec_Helper_Products $helper */
            $helper = Mage::helper('mnomap/products');
            $helper->pushToConnec($product);
        }
    }
}
