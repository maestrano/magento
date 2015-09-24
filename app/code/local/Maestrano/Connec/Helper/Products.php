<?php
class Maestrano_Connec_Helper_Products extends Maestrano_Connec_Helper_BaseMappers {

    public function __construct() {
        parent::__construct();
        $this->connec_entity_name = 'Product';
        $this->local_entity_name = 'Products';
        $this->connec_resource_name = 'items';
        $this->connec_resource_endpoint = 'items';
    }

    // Return a local Model by id
    public function loadModelById($localId)
    {
        $localModel = Mage::getModel('catalog/product')->load($localId);
        return $localModel;
    }

    // Return a new local Model
    protected function getNewModel()
    {
        return Mage::getModel('catalog/product');
    }

    // Map the Connec resource attributes onto the Magento model
    protected function mapConnecResourceToModel($product_hash, &$product)
    {
        // Fiels mapping
        $product->setTypeId('simple');
        if (array_key_exists('code', $product_hash)) { $product->setSku($product_hash['code']); }
        if (array_key_exists('name', $product_hash)) { $product->setName($product_hash['name']); }
        if (array_key_exists('description', $product_hash)) { $product->setDescription($product_hash['description']); }
        if (array_key_exists('sale_price', $product_hash)) {
            if (array_key_exists('net_amount', $product_hash['sale_price'])) {
                $product->setPrice($product_hash['sale_price']['net_amount']);
            }
        }
        if (array_key_exists('weight', $product_hash)) {
            $product->setWeight($product_hash['weight']);
        } else {
            $product->setWeight(0);
        }
        if (array_key_exists('status', $product_hash)) {
            if ($product_hash['status'] === 'ACTIVE') {
                $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            } else {
                $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            }
        }
        array_key_exists('sale_tax_code_id', $product_hash) ? $product->setTaxClassId(2) : $product->setTaxClassId(0); //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)

        // Set default values only if new object
        if($this->isNewByConnecId($product_hash['id'])) {
            // Product default visibility
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            // Set product stock level on product creation when specified
            if (array_key_exists('quantity_on_hand', $product_hash)) {
                $product->setStockData(array(
                        'qty' => $product_hash['quantity_on_hand']
                    )
                );
            } else {
                $product->setStockData(array(
                    'qty' => 0
                ));
            }
            // Short description set by default with description value
            if (array_key_exists('description', $product_hash)) {
                $product->setShortDescription($product_hash['description']);
            }

            $product->setAttributeSetId(Mage::getModel('catalog/product')->getDefaultAttributeSetId());
        }

        Mage::log("Maestrano_Connec_Helper_Products::mapConnecResourceToModel - mapped product: " . print_r($product->getData(), 1));
    }

    // Map the Magento model to a Connec resource hash
    protected function mapModelToConnecResource($product)
    {
        $product_hash = array();

        //Mage::log("Maestrano_Connec_Helper_Products::mapModelToConnecResource - product to map: " . print_r($product->getData(), 1));

        // Map attributes
        $product_hash['code'] = $product->getSku();
        $product_hash['name'] = $product->getName();
        $product_hash['description'] = $product->getDescription();
        $product_hash['sale_price'] = array();
        $product_hash['sale_price']['net_amount'] = $product->getPrice();
        $product_hash['weight'] = $product->getWeight();
        ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) ?
            $product_hash['status'] = 'ACTIVE' : $product_hash['status'] = 'INACTIVE';

        // Default product type to PURCHASED on creation
        if($this->isNewByLocalId($product->getId())) { $product_hash['type'] = 'PURCHASED'; }

        // Inventory
        $stockItem = $product->getStockItem();
        // Update stock only if stock tracking is enabled for this product
        if($stockItem && $stockItem->getManageStock()) {
          $product_hash['is_inventoried'] = true;
          if (!is_null($product->getStockItem())) { $product_hash['quantity_on_hand'] = $product->getStockItem()->getQty(); }
        }

        Mage::log("Maestrano_Connec_Helper_Products::mapModelToConnecResource - mapped product_hash: " . print_r($product_hash, 1));

        return $product_hash;
    }
}