<?php

/**
 * Class KL_Stock_Handlers_StockStatusHandler
 *
 * @package KL_Stock
 * @author  David Wickström <david@karlssonlord.com>
 */
class KL_Stock_Model_Handlers_StockStatusHandler
{
    /**
     * @var KL_Stock_ProductCollection
     */
    private $collection;

    /**
     * @var null
     */
    private $stockItem;

    /**
     * @param null                       $array
     * @param KL_Stock_ProductCollection $collection
     * @param null                       $stockItem
     */
    public function __construct(
        $array = null,
        KL_Stock_ProductCollection $collection = null,
        $stockItem = null
    ) {
        $this->collection = $collection ? : new KL_Stock_ProductCollection;
        $this->stockItem = $stockItem ? : Mage::getModel('cataloginventory/stock_item');
    }


    /**
     *  Start handling task
     */
    public function whenItsTimeToFixStockStatuses()
    {
        return $this
            ->handleSimpleProducts()
            ->handleConfigurableProducts();
    }

    /**
     *  Fix statuses for simple products
     *
     * @return $this
     */
    private function handleSimpleProducts()
    {
        foreach ($this->collection->getSimpleProducts() as $product) {
            $stockItem = $this->stockItem->loadByProduct($product);
            if (!$stockItem->getIsInStock() && $stockItem->getQty() > 0) {
                $this->correctStockStatus($product);
            }
        }

        return $this;
    }

    /**
     *  Fix statuses for configurable products
     *
     * @return $this
     */
    private function handleConfigurableProducts()
    {
        foreach ($this->collection->getConfigurableProducts() as $product) {
            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
            foreach ($childProducts as $childProduct) {
                $childStockItem = $this->stockItem->loadByProduct($childProduct);
                if ($childStockItem->getQty() > 0) {
                    $this->correctStockStatus($product);
                }
            }
        }

        return $this;
    }

    /**
     * @param $product
     */
    private function correctStockStatus($product)
    {
        try {
            $parentStockItem = $this->stockItem->loadByProduct($product);
            $parentStockItem->setIsInStock(1);
            $parentStockItem->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'exception.log', true);
        }
    }

}
