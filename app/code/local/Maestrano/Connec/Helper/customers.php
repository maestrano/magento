<?php
class Maestrano_Connec_Helper_Customers extends Maestrano_Connec_Helper_BaseMappers {

    public function __construct() {
        parent::__construct();
        $this->connec_entity_name = 'Customer';
        $this->local_entity_name = 'Customers';
        $this->connec_resource_name = 'people';
        $this->connec_resource_endpoint = 'people';
    }

    // Return a local Model by id
    public function loadModelById($localId)
    {
        $localModel = Mage::getModel('customer/customer')->load($localId);
        return $localModel;
    }

    // Map the Connec resource attributes onto the Magento model
    /**
     * @param array $customer_hash
     * @param Mage_Customer_Model_Customer $customer
     * @throws Mage_Core_Exception
     */
    protected function mapConnecResourceToModel($customer_hash, &$customer)
    {
        // Mapped values
        if (array_key_exists('title', $customer_hash)) { $customer->setPrefix($customer_hash['title']); }
        if (array_key_exists('first_name', $customer_hash)) { $customer->setFirstname($customer_hash['first_name']); }
        if (array_key_exists('last_name', $customer_hash)) { $customer->setLastname($customer_hash['last_name']); }
        if (array_key_exists('birth_date', $customer_hash)) { $customer->setDob($customer_hash['birth_date']); }
        if (array_key_exists('email', $customer_hash) && array_key_exists('address', $customer_hash['email'])) { $customer->setEmail($customer_hash['email']['address']); }

        // Default magento values
        if($this->isNewByConnecId($customer_hash['id'])) {
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->setGroupId(1); // 1: General Group
            $customer->setStore(Mage::app()->getStore());
        }

        // Customer addresses
        //$customer->get

        Mage::log("Maestrano_Connec_Helper_Customers::mapConnecResourceToModel - mapped customer: " . print_r($customer, 1));
    }

    /**
     * Map the Magento model to a Connec resource hash
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    protected function mapModelToConnecResource($customer)
    {
        $customer_hash = array();

        $billingAddress = $customer->getDefaultBillingAddress();

        // Mapped values
        $customer_hash['title'] = $customer->getPrefix();
        $customer_hash['first_name'] = $customer->getFirstname();
        $customer_hash['last_name'] = $customer->getLastname();
        $customer_hash['birth_date'] = $customer->getDob();
        $customer_hash['phone_home'] = array();
        $customer_hash['phone_home']['landline'] = $billingAddress->getTelephone();
        $customer_hash['phone_home']['fax'] = $billingAddress->getFax();
        $customer_hash['email'] = array();
        $customer_hash['email']['address'] = $customer->getEmail();

        // Default connec values
        if($this->isNewByLocalId($customer->getId())) {
            $customer_hash['is_customer'] = true;
            $customer_hash['organisation_id'] = true;
        }

        // Customer Addresses
        /*$customer_hash['address_home'] = array();
        $customer_hash['address_home']['billing'] = $this->mapModelAddressToConnecResource($customer->getDefaultBillingAddress());
        $customer_hash['address_home']['shipping'] = $this->mapModelAddressToConnecResource($customer->getDefaultShippingAddress());*/

        Mage::log("Maestrano_Connec_Helper_Customers::mapModelToConnecResource - mapped customer_hash: " . print_r($customer_hash, 1));

        return $customer_hash;
    }

    /**
     * @param $address
     * @return array address_hash
     */
    /*protected function mapModelAddressToConnecResource($address)
    {
        $address_hash = array();

        // Mapped values
        $address_hash['attention'] = $address->getFirstname() . ' ' . $address->getLastname();
        $address_hash['line1'] = $address->getStreet1();
        $address_hash['line2'] = $address->getStreet2();
        $address_hash['city'] = $address->getCity();
        $address_hash['region'] = $address->getRegion();
        $address_hash['postal_code'] = $address->getPostcode();
        $address_hash['country'] = $address->getCountry();

        return $address_hash;
    }*/

}