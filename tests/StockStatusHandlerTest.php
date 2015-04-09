<?php

use MageTest\Manager\Factory;

class StockStatusHandlerTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        Factory::prepareDb();
        $this->addAttributeToAttributeSet(92, 4);
        $this->addAttributeOption('color', 'Green');
    }

    public function tearDown()
    {
        Factory::clear();
    }
    /** @test */
    public function it_runs_through_a_collection_of_products_and_corrects_statuses()
    {
        /**
         * Create a simple and associate it with a configurable product. Both out of stock.
         */
        $simpleProduct = Factory::make('catalog/product', [
            'color' => 3,
            'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE,
        ]);
        $simpleProduct->save();
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simpleProduct);
        $stockItem->setManageStock(1);
        $stockItem->save();

        $this->assertEquals(0, $simpleProduct->getStockItem()->getQty());

        $configurableProduct = Factory::make('catalog/product', [
            'type_id' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            'name' => 'Testhans',
            'stock_data' => array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 1,
                'is_in_stock' => 0
            )
        ]);

        $product = $this->associateSimpleWithConfigurable($configurableProduct, $simpleProduct);

        /**
         * Check the configurable product is not in stock
         */
        $this->assertEquals('Testhans', $product->getName());
        $stock = $product->getStockItem();
        $this->assertEquals(0, $stock->getQty());

        /**
         * Put the simple product back in stock. Check for correct state.
         */
        $stockItem = $simpleProduct->getStockItem();
        $stockItem->setQty(1);
        $stockItem->setIsInStock(1);
        $stockItem->save();
        $this->assertEquals(1, $simpleProduct->getStockItem()->getIsInStock());
        $this->assertEquals(0, $product->getStockItem()->getIsInStock());

        /**
         *  Now run the machine to help correct the "In Stock"-status for the configurable product
         */
        (new KL_Stock_Model_Handlers_StockStatusHandler)->whenItsTimeToFixStockStatuses();

        $this->assertEquals(1, $product->load($product->getId())->getStockItem()->getIsInStock());


    }

    /**
     * @param       $configurableProduct
     * @param       $simpleProduct
     * @param array $usedAttributeIds
     * @param array $data
     * @return Mage_Catalog_Model_Product
     */
    private function associateSimpleWithConfigurable($configurableProduct, $simpleProduct, $usedAttributeIds = array(92), $data = array())
    {
        $configurableProduct->getTypeInstance()->setUsedProductAttributeIds($usedAttributeIds);
        $configurableAttributesData = $configurableProduct->getTypeInstance()->getConfigurableAttributesAsArray();
        $configurableProduct->setCanSaveConfigurableAttributes(true);
        $configurableProduct->setConfigurableAttributesData($configurableAttributesData);

        $data[$simpleProduct->getId()] = array(
            '0' => array(
                'label' => 'Green', //attribute label
                'attribute_id' => '92', //attribute ID of attribute 'color' in my store
                'value_index' => '3', //value of 'Green' index of the attribute 'color'
                'is_percent' => '0', //fixed/percent price for this option
                'pricing_value' => '21' //value for the pricing
            )
        );
        $configurableProduct->setConfigurableProductsData($data);
        return $configurableProduct->save();
    }

    private function addAttributeToAttributeSet($attributeId, $attributeSetId)
    {
        $model = Mage::getModel('eav/entity_setup','core_setup');
        $attributeGroupId = $model->getAttributeGroup('catalog_product', $attributeSetId, 'General');
        $model->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
    }

    private function addAttributeOption($attributeCode, $attributeValue)
    {
        $attributeModel = Mage::getModel('eav/entity_attribute');
        $attributeOptionsModel= Mage::getModel('eav/entity_attribute_source_table') ;

        $attributeId = $attributeModel->getIdByCode('catalog_product', $attributeCode);
        $attribute = $attributeModel->load($attributeId);

        $attributeOptionsModel->setAttribute($attribute);
        $options = $attributeOptionsModel->getAllOptions(false);

        foreach($options as $option) {
            // checking if already exists
            if ($option['label'] == $attributeValue) {
                $optionId = $option['value'];
                return $optionId;
            }
        }

        $value['option'] = array($attributeValue, $attributeValue);
        $result = array('value' => $value);
        $attribute->setData('option', $result);
        return $attribute->save();
    }

}
 