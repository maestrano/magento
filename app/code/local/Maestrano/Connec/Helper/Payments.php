<?php
class Maestrano_Connec_Helper_Payments extends Maestrano_Connec_Helper_BaseMappers {

    public function __construct() {
        parent::__construct();
        $this->connec_entity_name = 'Payment';
        $this->local_entity_name = 'Payments';
        $this->connec_resource_name = 'payments';
        $this->connec_resource_endpoint = 'payments';
    }

    // Return a local Model by id
    public function loadModelById($localId)
    {
        return Mage::getModel('sales/order_payment')->load($localId);
    }

    // Return a new local Model
    protected function getNewModel()
    {
        return Mage::getModel('sales/order_payment');
    }

    // Map the Connec resource attributes onto the Magento model
    protected function mapConnecResourceToModel($payment_hash, &$payment)
    {
        // Not saved locally, one way to connec!
    }

    // Map the Magento model to a Connec resource hash
    /**
     * @param Mage_Sales_Model_Order $payment
     * @return array
     */
    protected function mapModelToConnecResource($payment)
    {
        $payment_hash = array();
        $payment_hash['type'] = 'CUSTOMER';

        // Missing payment lines are considered as deleted by Connec!
        $payment_hash['opts'] = array('sparse' => false);

        /** @var Maestrano_Connec_Model_Mnoidmap $mnoIdMapModel */
        $mnoIdMapModel = Mage::getModel('connec/mnoidmap');

        // Customer order payment is made against
        $order = $payment->getOrder();

        // Invoice payment is made against
        $invoice = $order->hasInvoices() ? $order->getInvoiceCollection()->fetchItem() : null;

        $payment_hash['payment_reference'] = $order->getIncrementId();
        $payment_hash['private_note'] = 'Generated by Magento\nPayment for order #' . $order->getIncrementId() . " (" . $order->getCustomerFirstname() . " " . $order->getCustomerLastname() .  ")";
        $payment_hash['total_amount'] = $payment->getAmountPaid();
        $payment_hash['currency'] = $payment->getCurrencyCode();
        $payment_hash['payment_method'] = array('code' => $payment->getMethod());

        // Map customer
        $customerMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($order->getCustomerId(), Mage::helper('mnomap/customers')->getLocalResourceName());
        $payment_hash['person_id'] = $customerMnoIdMap['mno_entity_guid'];

        // Map payment transaction
        if($invoice) {
          // Add a single payment line for this invoice
          $invoiceMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($invoice->getId(), Mage::helper('mnomap/invoices')->getLocalResourceName());
          $linked_transaction = array('id' => $invoiceMnoIdMap['mno_entity_guid'], 'class' => 'Invoice');
        } else {
          // Add a single payment line for this order
          $orderMnoIdMap = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($order->getId(), Mage::helper('mnomap/salesorders')->getLocalResourceName());
          $linked_transaction = array('id' => $orderMnoIdMap['mno_entity_guid'], 'class' => 'SalesOrder');
        }
        $payment_line = array(
          'line_number' => 1,
          'amount' => $payment->getAmountPaid(),
          'linked_transactions' => array(0 => $linked_transaction)
        );
        $payment_hash['payment_lines'] = array(0 => $payment_line);

        Mage::log("Maestrano_Connec_Helper_Payments::mapModelToConnecResource - mapped order_hash: " . print_r($payment_hash, 1));

        return $payment_hash;
    }
}