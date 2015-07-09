<?php

class Maestrano_Connec_Model_ProductsObserver
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        /** @var Maestrano_Connec_Helper_Observerlockhelper $locker */
        $locker = Mage::helper('mnomap/observerlockhelper');
        if ($locker->isLockedGlobally()) {
            Mage::log("## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveAfter - Observers are locked globally");
            return;
        }

        $observerLock = $product->getObserverLock();
        if ($observerLock) {
            Mage::log("## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveAfter - Observers are locked for product " . $product->getId());
            return;
        }

        // Save product in connec!
        Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductSaveAfter: processing product: ' . $product->getId());
        /** @var Maestrano_Connec_Helper_Products $productMapper */
        $productMapper = Mage::helper('mnomap/products');
        $productMapper->processLocalUpdate($product);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductDeleteAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $observerLock = $product->getObserverLock();
        if ($observerLock) {
            Mage::log("## Maestrano_Connec_Model_ProductsObserver::catalogProductDeleteAfter - Observers are locked for product " . $product->getId());
            return;
        }

        // Delete product in connec_mnomapid!
        Mage::log('## Maestrano_Connec_Model_ProductsObserver::catalogProductDeleteAfter: deleting product ' . $product->getId());
        /** @var Maestrano_Connec_Helper_Products $helper */
        $helper = Mage::helper('mnomap/products');
        $helper->processLocalUpdate($product, false, true);
    }

}
