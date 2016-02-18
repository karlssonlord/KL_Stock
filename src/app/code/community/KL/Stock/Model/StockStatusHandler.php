<?php

/**
 * Class KL_Stock_StockStatusHandler
 *
 * @package KL_Stock
 * @author  David Wickström <david@karlssonlord.com>
 */
class KL_Stock_Model_StockStatusHandler
{
    /**
     *  Start handling task
     */
    public function whenItsTimeToFixStockStatuses()
    {
        Mage::log('KL_Stock job initiated', null, 'kl_stock.log', true);
        $this
            ->fixSimpleProducts()
            ->fixConfigurableProducts()
            ;
        Mage::log('KL_Stock ran successfully', null, 'kl_stock.log', true);
    }

    /**
     * @return $this
     */
    private function fixSimpleProducts()
    {
        foreach ($this->getSimpleProducts() as $simpleProduct) {
            $stockItem = $simpleProduct->load($simpleProduct->getId())->getStockItem();

            if ($this->statusIsNotInStock($stockItem) and $stockItem->getQty() > 0) {
                $this->correctStockStatusFor($stockItem);
                Mage::log($simpleProduct->getName() . ' had its status updated', null, 'kl_stock.log', true);
            }

            /**
             * Mark stock items as out of stock if qty is less than 1
             */
            if ($this->statusIsInStock($stockItem) and $stockItem->getQty() < 1) {
                $this->correctStockStatusFor($stockItem, 0);
                Mage::log($simpleProduct->getName() . ' had its status updated, set to out of stock since the qty is < 1', null, 'kl_stock.log', true);
            }

        }
        return $this;
    }

    /**
     *  Fix statuses for simple products
     *
     * @return $this
     */
    private function fixConfigurableProducts()
    {
        // Run through all configurable products that have stock status: is_in_stock 0
        foreach ($this->getConfigurableProducts() as $product) {
            $stockItem = $product->load($product->getId())->getStockItem();
            if ($this->statusIsNotInStock($stockItem) and $this->hasBabyProductInStock($product)) {
                $this->correctStockStatusFor($stockItem);
                Mage::log($product->getName() . ' had its status updated', null, 'kl_stock.log', true);
            } else if ($this->statusIsInStock($stockItem) and !$this->hasBabyProductInStock($product)) {
                $this->correctStockStatusFor($stockItem, 0);
                Mage::log($product->getName() . ' had its status updated', null, 'kl_stock.log', true);
            }
        }
        return $this;
    }

    /**
     * @return mixed
     */
    private function getConfigurableProducts()
    {
        return Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            ->addAttributeToSelect('type_id')
            ;
    }

    /**
     * @param $stockItem
     * @param $value
     * @return bool
     */
    private function correctStockStatusFor($stockItem, $value = 1)
    {
        $stockItem->setIsInStock($value);
        return $stockItem->save();
    }

    /**
     * @param $stockItem
     * @return bool
     */
    private function statusIsNotInStock($stockItem)
    {
        return $stockItem->getIsInStock() == 0;
    }

    /**
     * @param $stockItem
     * @return bool
     */
    private function statusIsInStock($stockItem)
    {
        return $stockItem->getIsInStock() == 1;
    }

    /**
     * @param $configurableProduct
     * @return bool
     */
    private function hasBabyProductInStock($configurableProduct)
    {
        foreach ($this->getAssociatedProductsFor($configurableProduct) as $simpleProduct) {
            if ($this->simpleProductIsSalable($simpleProduct)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $configurableProduct
     * @return mixed
     */
    private function getAssociatedProductsFor($configurableProduct)
    {
        return Mage::getModel('catalog/product_type_configurable')->setProduct($configurableProduct)
            ->getUsedProductCollection()
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions()
            ;
    }

    /**
     * @param $simpleProduct
     * @return bool
     */
    private function simpleProductIsSalable($simpleProduct)
    {
        $stockItem = $simpleProduct->getStockItem();
        if ($stockItem->getIsInStock()) return true;
        return false;
    }

    /**
     * @return mixed
     */
    private function getSimpleProducts()
    {
        return Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->addAttributeToSelect('type_id')
            ;
    }

}