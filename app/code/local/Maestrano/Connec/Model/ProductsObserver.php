<?php

class Maestrano_Connec_Model_ProductsObserver
{

    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductSaveBefore(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        Mage::log('## catalogProductSaveBefore: ' . $product->getId());

        // Save product in connec!
        /** @var Maestrano_Connec_Helper_Products $helper */
        $helper = Mage::helper('mnomap/products');
        $test = $helper->loadModelById($product->getId());

        Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveBefore - isObjectNew: ' . $product->isObjectNew());
        //Mage::log('## product: ' . print_r($product, 1));

        $helper->processLocalUpdate($product);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    /*public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        Mage::log('## catalogProductSaveAfter: ' . $product->getId());

        // Save product in connec!
        $helper = Mage::helper('test/products');
        $test = $helper->loadModelById($product->getId());

        // $product->isObjectNew() isn't set in catalog_product_save_after
        Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveAfter - isObjectNew: ' . $product->isObjectNew());
        //Mage::log('## product: ' . print_r($product, 1));

        //$helper->processLocalUpdate($product);
    }*/

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
