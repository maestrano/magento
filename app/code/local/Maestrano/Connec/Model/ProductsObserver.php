<?php

class Maestrano_Connec_Model_ProductsObserver
{

    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductSaveBefore(Varien_Event_Observer $observer)
    {
//        $product = $observer->getEvent()->getProduct();
//        Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveBefore - product: ' . print_r($product, 1));
//        Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveBefore - isObjectNew: ' . $product->isObjectNew());
//        Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveBefore - id: ' . $product->getId());
//
//        // Save product in connec!
//        /** @var Maestrano_Connec_Helper_Products $helper */
//        $helper = Mage::helper('mnomap/products');
//        $helper->processLocalUpdate($product);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        //Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveAfter - product: ' . print_r($product, 1));
        Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveAfter - isObjectNew: ' . $product->isObjectNew());
        Mage::log('## catalogProductSaveAfter: ' . $product->getId());

        // Save product in connec!
        /** @var Maestrano_Connec_Helper_Products $helper */
        $helper = Mage::helper('mnomap/products');
        $helper->processLocalUpdate($product);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductDeleteAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        Mage::log('## catalogProductDeleteAfter: ' . $product->getId());

        // Delete product in connec!
    }

}
