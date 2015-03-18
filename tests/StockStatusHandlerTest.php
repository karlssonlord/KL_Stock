<?php

class StockStatusHandlerTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_runs_through_a_collection_of_products_and_corrects_statuses()
    {
        $stockItemMock = Mockery::mock('StockItemDummy');
        $stockItemMock->shouldReceive('getIsInStock')->andReturn(0);
        $stockItemMock->shouldReceive('getItemQty')->andReturn(1);
        $stockItemMock->shouldReceive('setIsInStock')->andReturn($stockItemMock);
        $stockItemMock->shouldReceive('save');

        $productMock = Mockery::mock('ProductDummy');
        $productMock->shouldReceive('getStockItem')->andReturn($stockItemMock);

        $productsCollectionMock = Mockery::mock('CollectionDummy');
        $productsCollectionMock->shouldReceive()->andReturn(array($productMock));

        $collectionMock = Mockery::mock('KL_Stock_ProductCollection');
        $collectionMock->shouldReceive('getSimpleProducts')->once()->andReturn($productsCollectionMock);
        $collectionMock->shouldReceive('getConfigurableProducts')->once()->andReturn($productsCollectionMock);

        $handler = new KL_Stock_Handlers_StockStatusHandler(null, $collectionMock);
        $return = $handler->whenItsTimeToFixStockStatuses();

        $this->assertInstanceOf('KL_Stock_Handlers_StockStatusHandler', $return);
    }

}
 