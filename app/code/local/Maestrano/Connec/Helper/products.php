<?php
class Maestrano_Connec_Helper_Products extends Maestrano_Connec_Helper_BaseMappers {

    public function __construct() {
        $this->connec_entity_name = 'Product';
        $this->local_entity_name = 'Products';
        $this->connec_resource_name = 'items';
        $this->connec_resource_endpoint = 'items';
    }

    // Return a local Model by id
    public function loadModelById($localId)
    {
        $localModel = Mage::getModel('catalog/product')->load($localId);;
        return $localModel;
    }

    // Map the Connec resource attributes onto the Magento model
    protected function mapConnecResourceToModel($product_hash, &$product)
    {
        // Fiels mapping
        if (array_key_exists('code', $product_hash)) { $product->setSku($product_hash['code']); }
        if (array_key_exists('name', $product_hash)) { $product->setName($product_hash['name']); }
        if (array_key_exists('description', $product_hash)) { $product->setDescription($product_hash['description']); }
        if (array_key_exists('sale_price', $product_hash)) {
            if (array_key_exists('net_amount', $product_hash['sale_price'])) {
                $product->setPrice($product_hash['sale_price']['net_amount']);
            }
        }
        if (array_key_exists('weight', $product_hash)) { $product->setWeight($product_hash['weight']); }
        if (array_key_exists('status', $product_hash)) {
            if ($product_hash['status'] === 'ACTIVE') {
                $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            } else {
                $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            }
        }
        array_key_exists('sale_tax_code_id', $product_hash) ? $product->setTaxClassId(2) : $product->setTaxClassId(0); //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)

        // Set default values only if new object
        if($this->is_new($product)) {
            // Product default visibility
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            // Set product stock level on product creation when specified
            if (array_key_exists('initial_quantity', $product_hash)) {
                $product->setStockData(array(
                        'qty' => $product_hash['initial_quantity']
                    )
                );
            } else if (array_key_exists('quantity_on_hand', $product_hash)) {
                $product->setStockData(array(
                        'qty' => $product_hash['quantity_on_hand']
                    )
                );
            }
            // Short description set by default with description value
            if (array_key_exists('description', $product_hash)) {
                $product->setShortDescription($product_hash['description']);
            }

            $product->setAttributeSetId(4); // 9 is for default
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
        ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) ?
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
}