<?php

/**
 * Class KL_Stock_Handlers_StockStatusHandler
 * @package KL_Stock
 * @author David Wickström <david@karlssonlord.com>
 */
class KL_Stock_Handlers_StockStatusHandler
{

    /**
     * @var KL_Stock_ProductCollection
     */
    private $collection;

    /**
     * @param null                       $array
     * @param KL_Stock_ProductCollection $collection
     */
    public function __construct($array = null, KL_Stock_ProductCollection $collection = null)
    {
        $this->collection = $collection ? : new KL_Stock_ProductCollection;
    }

    /**
     *  Start handling task
     */
    public function whenItsTimeToFixStockStatuses()
    {
        return $this
            ->handleSimpleProducts()
            ->handleConfigurableProducts()
        ;
    }

    /**
     *  Fix statuses for simple products
     *
     * @return $this
     */
    private function handleSimpleProducts()
    {
        foreach ($this->collection->getSimpleProducts() as $product) {
            $stockItem = $product->getStockItem();
            if (!$stockItem->getIsInStock() && $stockItem->getItemQty() > 0) {
                $stockItem->setIsInStock(1)->save();
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
            foreach ($product->getAllItems() as $item) {
                $childProduct = $this->product->load($item->getId());
                $childStockItem = $childProduct->getStockItem();
                if ($childStockItem->getItemQty() > 0) {
                    $parentStockItem = $product->getStockItem();
                    $parentStockItem->setIsInStock(1)->save();
                }
            }
        }
        return $this;
    }

}
