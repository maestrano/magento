<?php
class Maestrano_Connec_Helper_Invoices extends Maestrano_Connec_Helper_BaseMappers {

    public function __construct() {
        parent::__construct();
        $this->connec_entity_name = 'SalesOrderInvoice';
        $this->local_entity_name = 'SalesOrderInvoices';
        $this->connec_resource_name = 'invoices';
        $this->connec_resource_endpoint = 'invoices';
    }

    // Return a local Model by id
    public function loadModelById($localId)
    {
        $localModel = Mage::getModel('sales/order_invoice')->load($localId);
        return $localModel;
    }

    // Return a new local Model
    protected function getNewModel()
    {
        return Mage::getModel('sales/order_invoice');
    }

    // Map the Connec resource attributes onto the Magento model
    protected function mapConnecResourceToModel($order_hash, &$order)
    {
        // Not saved locally, one way to connec!
    }

    // Map the Magento model to a Connec resource hash
    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return array
     */
    protected function mapModelToConnecResource($invoice)
    {
        $invoice_hash = array();

        // Missing transaction lines are considered as deleted by Connec!
        $invoice_hash['opts'] = array('sparse' => false);

        /** @var Maestrano_Connec_Model_Mnoidmap $mnoIdMapModel */
        $mnoIdMapModel = Mage::getModel('connec/mnoidmap');

        // Get customer mno_id_map
        $customerMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($invoice->getOrder()->getCustomerId(), Mage::helper('mnomap/customers')->getLocalResourceName());
        $orderMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($invoice->getOrderId(), Mage::helper('mnomap/salesorders')->getLocalResourceName());

        $invoice_hash['due_date'] = $invoice->getCreatedAtDate()->toString(Zend_Date::ISO_8601);
        $invoice_hash['transaction_date'] = $invoice->getCreatedAtDate()->toString(Zend_Date::ISO_8601);
        $invoice_hash['title'] = 'Magento invoice #' . $invoice->getIncrementId() . " (" . $invoice->getOrder()->getCustomerFirstname() . " " . $invoice->getOrder()->getCustomerLastname() .  ")";
        $invoice_hash['private_note'] = "Generated by Magento\n" . $invoice_hash['title'];
        $invoice_hash['person_id'] = $customerMnoIdMap['mno_entity_guid'];
        $invoice_hash['sales_order_id'] = $orderMnoIdMap['mno_entity_guid'];

        // State
        $invoice_hash['status'] = strtoupper($invoice->getStateName());

        // Address
        $billingAddress = $invoice->getBillingAddress();
        $shippingAddress = $invoice->getShippingAddress();
        $billing = array(
          'attention_first_name' => $billingAddress->getFirstname(),
          'attention_last_name' => $billingAddress->getLastname(),
          'line1' => $billingAddress->getStreet(1),
          'line2' => $billingAddress->getStreet(2),
          'city' => $billingAddress->getCity(),
          'postal_code' => $billingAddress->getPostcode(),
          'region' => $billingAddress->getRegion(),
          'country' => $billingAddress->getCountry()
        );

        $shipping = array(
          'attention_first_name' => $shippingAddress->getFirstname(),
          'attention_last_name' => $shippingAddress->getLastname(),
          'line1' => $shippingAddress->getStreet(1),
          'line2' => $shippingAddress->getStreet(2),
          'city' => $shippingAddress->getCity(),
          'postal_code' => $shippingAddress->getPostcode(),
          'region' => $shippingAddress->getRegion(),
          'country' => $shippingAddress->getCountry()
        );

        $invoice_hash['billing_address'] = $billing;
        $invoice_hash['shipping_address'] = $shipping;

        // Map invoice items
        $items = $invoice->getAllItems();
        if (count($items) > 0) {
            $invoice_hash['lines'] = array();
            foreach($items as $item) {
                // If a product is a configured, line are doubled (configured and simple)
                // We only keep the configured product and interogate db to get simple product id
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                $line_hash = array();

                // Configurable and simple product both have the simple product sku
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
                // Get product mno_id_map
                $productMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($product->getId(), Mage::helper('mnomap/products')->getLocalResourceName());
                $line_hash['item_id'] = $productMnoIdMap['mno_entity_guid'];
                $line_hash['description'] = $item->getName();
                $line_hash['quantity'] = $item->getQty();
                $line_hash['unit_price'] = array();
                $line_hash['unit_price']['total_amount'] = $item->getBasePriceInclTax();
                $line_hash['unit_price']['tax_rate'] = $item->getOrderItem()->getTaxPercent();
                $line_hash['total_price'] = array();
                $line_hash['total_price']['total_amount'] = $item->getRowTotalInclTax();
                $line_hash['total_price']['tax_rate'] = $item->getOrderItem()->getTaxPercent();

                $invoice_hash['lines'][] = $line_hash;
            }
        }

        Mage::log("Maestrano_Connec_Helper_Salesorders::mapModelToConnecResource - mapped invoice_hash: " . print_r($invoice_hash, 1));

        return $invoice_hash;
    }
}