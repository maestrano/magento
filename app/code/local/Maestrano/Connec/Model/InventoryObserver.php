<?php

class Maestrano_Connec_Model_InventoryObserver
{
    /**
     * Gets triggered on sale
     * @param Varien_Event_Observer $observer
     */
    public function catalogInventorySave(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getItem();

        if ((int)$item->getData('qty') != (int)$item->getOrigData('qty')) {
            $product = $item->getProduct();
            if (is_null($product)) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
            }

            $product->getStockItem()->setQty($item->getQty());
            Mage::log('## Maestrano_Connec_Model_InventoryObserver::catalogInventorySave: updating product:' . $product->getId() . ', qty: ' . $product->getStockItem()->getQty());

            /** @var Maestrano_Connec_Helper_Products $helper */
            $helper = Mage::helper('mnomap/products');
            $helper->pushToConnec($product);
        }
    }

    /**
     * Is triggered when a customer place an order
     * @param Varien_Event_Observer $observer
     */
    public function subtractQuoteInventory(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        Mage::log('## Maestrano_Connec_Model_InventoryObserver::subtractQuoteInventory: quote:' . print_r($quote->getData(), 1));

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProduct()->getTypeId() != 'simple'){
                continue;
            }

            // Update those items
            $delta = ($item->getTotalQty() * -1);
            Mage::log('## Maestrano_Connec_Model_InventoryObserver::subtractQuoteInventory: updating product:' . $item->getProduct()->getId() . ', qty: ' . $item->getProduct()->getStockItem()->getQty() . ", delta: " . $delta);
            $newQty = $item->getProduct()->getStockItem()->getQty() + $delta;
            $item->getProduct()->getStockItem()->setQty($newQty);
            /** @var Maestrano_Connec_Helper_Products $helper */
            $helper = Mage::helper('mnomap/products');
            $helper->pushToConnec($item->getProduct());
        }
    }

    public function revertQuoteInventory(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        Mage::log('## Maestrano_Connec_Model_InventoryObserver::revertQuoteInventory: quote:' . print_r($quote->getData(), 1));

        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            if (is_null($product)) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
            }

            if ($product->getTypeId() != 'simple'){
                continue;
            }

            // Update those items
            Mage::log('## Maestrano_Connec_Model_InventoryObserver::revertQuoteInventory: updating product:' . $product->getId() . ', qty: ' . $product->getStockItem()->getQty() . ", delta: " . $item->getTotalQty());
            /** @var Maestrano_Connec_Helper_Products $helper */
            $helper = Mage::helper('mnomap/products');
            $helper->pushToConnec($product);
        }
    }

    public function refundOrderInventory(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();

        Mage::log('## Maestrano_Connec_Model_InventoryObserver::refundOrderInventory: creditmemo:' . print_r($creditmemo->getData(), 1));

        foreach ($creditmemo->getAllItems() as $item) {
            $product = $item->getProduct();
            if (is_null($product)) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
            }

            if ($product->getTypeId() != 'simple'){
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
