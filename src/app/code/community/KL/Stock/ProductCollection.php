<?php

/**
 * Class KL_Stock_ProductCollection
 * @package KL_Stock
 * @author David Wickström <david@karlssonlord.com>
 */
class KL_Stock_ProductCollection
{

    /**
     * @var Mage_Catalog_Model_Product
     */
    private $product;

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function __construct(Mage_Catalog_Model_Product $product = null)
    {
        $this->product = $product ? : Mage::getModel('catalog/product');
    }

    /**
     * @return mixed
     */
    public function getConfigurableProducts()
    {
        return $this->product
            ->getCollection()
            ->addAttributeToFilter('product_type', 'configurable')
            ->addAttributeToFilter('inventory_stock_availability', 0)
            ->addAttributeToSelect('is_in_stock');
    }

    /**
     * @return mixed
     */
    public function getSimpleProducts()
    {
        return $this->product
            ->getCollection()
            ->addAttributeToFilter('product_type', 'simple')
            ->addAttributeToFilter('inventory_stock_availability', 0)
            ->addAttributeToFilter('inventory_qty', array('gt' => 0))
            ->addAttributeToSelect('is_in_stock');
    }
} 