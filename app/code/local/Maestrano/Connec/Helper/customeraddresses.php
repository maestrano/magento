<?php
class Maestrano_Connec_Helper_Customeraddresses extends Maestrano_Connec_Helper_BaseMappers {

    public function __construct() {
        parent::__construct();
        $this->connec_entity_name = 'Address';
        $this->local_entity_name = 'Addresses';
        $this->connec_resource_name = 'people';
        $this->connec_resource_endpoint = 'people';
    }

    // Return a local Model by id
    public function loadModelById($localId)
    {
        $localModel = Mage::getModel("customer/customeraddresses")->load($localId);
        return $localModel;
    }

    // Map the Connec resource attributes onto the Magento model
    protected function mapConnecResourceToModel($customer_hash, &$address)
    {
        // Mapped values


        Mage::log("Maestrano_Connec_Helper_Customers::mapConnecResourceToModel - mapped address: " . print_r($address, 1));
    }

    /**
     * @param array $address_hash
     * @param Mage_Customer_Model_Address $address
     */
    protected function mapConnecAddressToModel($address_hash, &$address)
    {
        // Mapped values
        if (array_key_exists('attention', $address_hash)) {
            list($firstname, $lastname) = explode(' ', $address_hash['attention'], 2);
            $address->setFirstname($firstname);
            $address->setLastname($lastname);
        }
        if (array_key_exists('line1', $address_hash)) { $address->setStreet1($address_hash['line1']); }
        if (array_key_exists('line2', $address_hash)) { $address->setStreet2($address_hash['line2']); }
        if (array_key_exists('city', $address_hash)) { $address->setCity($address_hash['city']); }
        if (array_key_exists('region', $address_hash)) { $address->setRegion($address_hash['region']); }
        if (array_key_exists('postal_code', $address_hash)) { $address->setPostcode($address_hash['postal_code']); }
        if (array_key_exists('country', $address_hash)) { $address->setCountry( $address_hash['country']); }
    }

    /**
     * Map the Magento model to a Connec resource hash
     * @param Mage_Customer_Model_Address $address
     * @return array
     */
    protected function mapModelToConnecResource($address)
    {
        $customer_hash = array();

        if ($this->isDefaultBilling($address) || $this->isDefaultShipping($address)) {
            $customer_hash['address_home'] = array();

            if ($this->isDefaultBilling($address)) {
                $customer_hash['phone_home'] = array();
                $customer_hash['phone_home']['landline'] = $address->getTelephone();
                $customer_hash['phone_home']['fax'] = $address->getFax();
                $customer_hash['address_home']['billing'] = $this->mapModelAddressToConnecResource($address);
            }

            if ($this->isDefaultShipping($address)) {
                $customer_hash['address_home']['shipping'] = $this->mapModelAddressToConnecResource($address);
            }
        }

        Mage::log("Maestrano_Connec_Helper_Customeraddresses::mapModelToConnecResource - mapped customer_hash: " . print_r($customer_hash, 1));

        return $customer_hash;
    }

    /**
     * @param Mage_Customer_Model_Address $address
     * @return boolean
     */
    private function isDefaultBilling($address)
    {
        $defaultBillingId = $address->getCustomer()->getDefaultBillingAddress()->getId();
        if ($defaultBillingId === $address->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @param Mage_Customer_Model_Address $address
     * @return boolean
     */
    private function isDefaultShipping($address)
    {
        $defaultShippingId = $address->getCustomer()->getDefaultShippingAddress()->getId();
        if ($defaultShippingId === $address->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @param $address
     * @return array address_hash
     */
    protected function mapModelAddressToConnecResource($address)
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
    }
}