<?php
class Maestrano_Connec_Helper_Salesorders extends Maestrano_Connec_Helper_BaseMappers {

    public function __construct() {
        parent::__construct();
        $this->connec_entity_name = 'SalesOrder';
        $this->local_entity_name = 'SalesOrders';
        $this->connec_resource_name = 'sales_orders';
        $this->connec_resource_endpoint = 'sales_orders';
    }

    // Return a local Model by id
    public function loadModelById($localId)
    {
        $localModel = Mage::getModel('sales/order')->load($localId);
        return $localModel;
    }

    // Return a new local Model
    protected function getNewModel()
    {
        return Mage::getModel('sales/order');
    }

    // Map the Connec resource attributes onto the Magento model
    protected function mapConnecResourceToModel($order_hash, &$order)
    {

    }

    // Map the Magento model to a Connec resource hash
    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function mapModelToConnecResource($order)
    {
        $order_hash = array();

        /** @var Maestrano_Connec_Model_Mnoidmap $mnoIdMapModel */
        $mnoIdMapModel = Mage::getModel('connec/mnoidmap');

        // Get customer mno_id_map
        $customerMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($order->getCustomerId(), Mage::helper('mnomap/customers')->getLocalResourceName());

        $order_hash['due_date'] = $order->getCreatedAtDate()->toString(Zend_Date::ISO_8601);
        $order_hash['transaction_date'] = $order->getCreatedAtDate()->toString(Zend_Date::ISO_8601);
        $order_hash['title'] = 'Magento order #' . $order->getIncrementId() . " (" . $order->getCustomerFirstname() . " " . $order->getCustomerLastname() .  ")";
        $order_hash['person_id'] = $customerMnoIdMap['mno_entity_guid'];
        $order_hash['status'] = 'PAID';

        $items = $order->getAllItems();
        if (count($items) > 0) {
            $order_hash['lines'] = array();
            foreach($items as $item) {
                // Get product mno_id_map
                $productMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($item->getProductId(), Mage::helper('mnomap/products')->getLocalResourceName());

                // Item is known
                if ($productMnoIdMap) {
                    if ($item->getParentItem()) {
                        // If there is a parent item, it contains sales infos
                        $item = $item->getParentItem();
                    }

                    $line_hash = array();

                    $line_hash['item_id'] = $productMnoIdMap['mno_entity_guid'];
                    $line_hash['description'] = $item->getName();
                    $line_hash['quantity'] = $item->getQtyOrdered();
                    $line_hash['unit_price'] = array();
                    $line_hash['unit_price']['total_amount'] = $item->getRowTotal();
                    $line_hash['unit_price']['net_amount'] = $item->getBasePrice();
                    $line_hash['unit_price']['tax_amount'] = $item->getTaxAmount();
                    $line_hash['unit_price']['tax_rate'] = $item->getTaxPercent();

                    $order_hash['lines'][] = $line_hash;
                }
            }
        }

        Mage::log("Maestrano_Connec_Helper_Salesorders::mapModelToConnecResource - mapped $order_hash: " . print_r($order_hash, 1));

        return $order_hash;
    }
}