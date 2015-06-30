<?php
class Maestrano_Connec_Helper_Products extends Maestrano_Connec_Helper_BaseMappers {

    public function __construct() {
        $this->connec_entity_name = 'Product';
        $this->local_entity_name = 'Products';
        $this->connec_resource_name = 'items';
        $this->connec_resource_endpoint = 'items';
    }

    // Return the Model local id
    protected function getId($model)
    {
        $model->getId();
    }

    // Return a local Model by id
    public function loadModelById($localId)
    {
        $localModel = Mage::getModel('catalog/product')->load($localId);;
        return $localModel;
    }

    // Map the Connec resource attributes onto the Magento model
    protected function mapConnecResourceToModel($product_hash, $product)
    {
        // Mandatory fields
        if ($this->is_set($product_hash['code'])) { $product->setSku($product_hash['code']); }
        if ($this->is_set($product_hash['name'])) { $product->setName($product_hash['name']); }
        if ($this->is_set($product_hash['description'])) { $product->setDescription($product_hash['description']); }
        if ($this->is_set($product_hash['sale_price'])) {
            if ($this->is_set($product_hash['sale_price']['net_amount'])) { $product->setPrice($product_hash['sale_price']['net_amount']); }
        }
        if ($this->is_set($product_hash['weight'])) { $product->setWeight($product_hash['weight']); }
        $this->is_set($product_hash['status'] === 'ACTIVE') ? $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED) : $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->is_set($product_hash['sale_tax_code_id']) ? $product->setTaxClassId(2) : $product->setTaxClassId(0); //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)

        // Set default values only if new object
        if ($this->is_new($product)) { $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH); }
        if ($this->is_new($product) && $this->is_set($product_hash['description'])) { $product->setShortDescription($product_hash['description']); }

        // Set product stock level on Product creation when specified
        if($this->is_new($product)) {
            $product->setStockData(array(
                    'qty' => is_null($product_hash['initial_quantity']) ? $product_hash['quantity_on_hand'] : $product_hash['initial_quantity']
                )
            );
        }
    }

    // Map the Magento model to a Connec resource hash
    protected function mapModelToConnecResource($product)
    {
        $product_hash = array();

        // Default product type to PURCHASED on creation
        if($this->is_new($product)) { $product_hash['type'] = 'PURCHASED'; }

        // Map attributes
        $product_hash['code'] = $product->getSku();
        $product_hash['name'] = $product->getName();
        $product_hash['description'] = $product->getDescription();
        $product_hash['sale_price'] = $product->getPrice();
        $product_hash['weight'] = $product->getWeight();
        ($product->getStatus() === Mage_Catalog_Model_Product_Status::STATUS_ENABLED) ?
            $product_hash['status'] = 'ACTIVE' : $product_hash['status'] = 'INACTIVE';

        // TODO: Implement this in InventoryOberver
        // TODO: What if a new qty is entered?
        // Inventory tracking
        /*$qty = $product->column_fields['qtyinstock'];
        $qtyindemand = $product->column_fields['qtyindemand'];
        $unit_price = $product->column_fields['unit_price'];
        if($this->is_set($qtyinstock) && $this->is_set($qtyindemand)) {
            $product_hash['quantity_on_hand'] = $qtyinstock;
            $product_hash['quantity_committed'] = $qtyindemand;
            $product_hash['quantity_available'] = $qtyinstock - $qtyindemand;
            $product_hash['average_cost'] = $qtyinstock * $unit_price;
            $product_hash['current_value'] = $unit_price;
        }*/

        return $product_hash;
    }

    // Persist the Magento model
    protected function persistLocalModel($model, $resource_hash)
    {
        $model->save();
    }
}