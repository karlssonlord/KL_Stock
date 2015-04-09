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
            ->addAttributeToFilter('type_id', 'configurable')
            ->addAttributeToFilter('inventory_stock_availability', 0)
            ->addAttributeToSelect('*');
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
//        return Mage::getModel('catalog/product')
//            ->getCollection()
//            ->joinField(
//                'qty',
//                'cataloginventory/stock_item',
//                'qty',
//                'product_id=entity_id',
//                '{{table}}.stock_id=1',
//                'left'
//            )
//            ->addAttributeToFilter('qty', array('eq' => 0))
//            ->addAttributeToSelect('*')
//        ;
        return Mage::getModel('cataloginventory/stock_item')
            ->getCollection()
            ->addQtyFilter('>', 0)
            ;
    }
} 