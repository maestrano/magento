<?php
class Maestrano_Connec_Helper_Customeraddresses extends Maestrano_Connec_Helper_BaseMappers {

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
        $localModel = Mage::getModel('customer/customeraddresses')->load($localId);
        return $localModel;
    }

    protected function getNewModel()
    {
        return Mage::getModel('customer/customeraddresses');
    }

    // Map the Connec resource attributes onto the Magento model
    protected function mapConnecResourceToModel($customer_hash, &$address)
    {
        // Is not used for address
        // Addresses are mapped in the customer helper
    }

    /**
     * Map the Magento model to a Connec resource hash
     * @param Mage_Customer_Model_Address $address
     * @return array
     */
    protected function mapModelToConnecResource($address)
    {
        $customer_hash = array();

        //Mage::log("Maestrano_Connec_Helper_Customeraddresses::mapModelToConnecResource - address to map: " . print_r($address->getData(), 1));

        if ($this->isDefaultBilling($address) || $this->isDefaultShipping($address)) {
            $customer_hash['address_work'] = array();

            if ($this->isDefaultBilling($address)) {
                $customer_hash['phone_home'] = array();
                $customer_hash['phone_home']['landline'] = $address->getTelephone();
                $customer_hash['phone_home']['fax'] = $address->getFax();
                $customer_hash['address_work']['billing'] = $this->mapModelAddressToConnecResource($address);
            }

            if ($this->isDefaultShipping($address)) {
                $customer_hash['address_work']['shipping'] = $this->mapModelAddressToConnecResource($address);
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
        $defaultBilling = $address->getCustomer()->getDefaultBillingAddress();
        if ($defaultBilling) {
            $defaultBillingId = $defaultBilling->getId();
            if ($defaultBillingId === $address->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Mage_Customer_Model_Address $address
     * @return boolean
     */
    private function isDefaultShipping($address)
    {
        $defaultShipping = $address->getCustomer()->getDefaultShippingAddress();
        if ($defaultShipping) {
            $defaultShippingId = $defaultShipping->getId();
            if ($defaultShippingId === $address->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Mage_Customer_Model_Address $address
     * @return array address_hash
     */
    protected function mapModelAddressToConnecResource($address)
    {
        $address_hash = array();

        // Mapped values
        $address_hash['attention_first_name'] = $address->getFirstname();
        $address_hash['attention_last_name'] = $address->getLastname();
        $address_hash['line1'] = $address->getStreet1();
        $address_hash['line2'] = $address->getStreet2();
        $address_hash['city'] = $address->getCity();
        $address_hash['region'] = $address->getRegion();
        $address_hash['postal_code'] = $address->getPostcode();
        $address_hash['country'] = $address->getCountry();

        return $address_hash;
    }

    /**
     * OVERRIDED
     * Push an address to connec
     * Specific behavior for addresses
     * @param Mage_Customer_Model_Address $model
     * @return bool|void
     */
    public function pushToConnec($model) {
        // Find local id
        $customer_id = $model->getCustomer()->getId();
        Mage::log("Maestrano_Connec_Helper_Customeraddresses::pushToConnec entity=$this->connec_entity_name, customer_id=$customer_id");

        // Transform the Model into a Connec hash
        $resource_hash = $this->mapModelToConnecResource($model);
        if (empty($resource_hash)) {
            return;
        }

        $hash = array($this->connec_resource_name => $resource_hash);

        // Find Connec resource id
        $mno_id_map = Mage::getModel('connec/mnoidmap')->findMnoIdMapByLocalIdAndEntityName($customer_id, $this->local_entity_name);

        // Update resource in connect
        $url = $this->connec_resource_endpoint . '/' . $mno_id_map['mno_entity_guid'];
        Mage::log("Maestrano_Connec_Helper_BaseMappers::pushToConnec updating entity=$this->local_entity_name, url=$url, id=$customer_id hash=" . json_encode($hash));
        $response = $this->_connec_client->put($url, $hash);

        // Process Connec response
        $code = $response['code'];
        $body = $response['body'];
        if($code >= 300) {
            Mage::log("Maestrano_Connec_Helper_Customeraddresses::pushToConnec Cannot push to Connec! entity_name=$this->local_entity_name, code=$code, body=$body");
            return false;
        } else {
            Mage::log("Maestrano_Connec_Helper_Customeraddresses::pushToConnec Processing Connec! response code=$code, body=$body");
        }
    }
}