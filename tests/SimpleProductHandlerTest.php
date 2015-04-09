<?php

use MageTest\Manager\Factory;

class SimpleProductHandlerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        Factory::prepareDb();
    }

    public function tearDown()
    {
        Factory::clear();
        Factory::prepareDb();
    }

    /** @test */
    public function it_runs_through_a_collection_of_simple_products_and_corrects_statuses()
    {
        /**
         * Create a simple and associate it with a configurable product. Both out of stock.
         */

        $simpleProduct = Factory::make('catalog/product', [
            'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        ]);

        $simpleProduct->save();
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simpleProduct);
        $stockItem->setManageStock(1);
        $stockItem->save();

        $this->assertEquals(0, $simpleProduct->load($simpleProduct->getId())->getStockItem()->getQty());
        $this->assertEquals(0, $simpleProduct->getStockItem()->getIsInStock());

        $stockItem = $simpleProduct->getStockItem();
        $stockItem->setQty(666);
        $stockItem->save();

        $this->assertEquals(666, $simpleProduct->load($simpleProduct->getId())->getStockItem()->getQty());
        $this->assertEquals(0, $simpleProduct->getStockItem()->getIsInStock());

        /**
         *  Now run the machine to help correct the "In Stock"-status for the simple product
         */
        (new KL_Stock_Model_StockStatusHandler)->whenItsTimeToFixStockStatuses();

        $this->assertEquals(1, $simpleProduct->load($simpleProduct->getId())->getStockItem()->getIsInStock());
    }
}
