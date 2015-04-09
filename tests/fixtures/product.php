<?php

return array(
    'catalog/product' => array(
        'attributes' => array(
            'sku' => $this->faker->ean,
            'attribute_set_id' => 4,
            'name' => $this->faker->name,
            'weight' => 2,
            'price' => 100,
            'description' => $this->faker->text,
            'short_description' => $this->faker->text,
            'tax_class_id' => 1,
            'type_id' => 'simple',
            'visibility' => 4,
            'status' => 1,
            'stock_data' => array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 1,
                'min_sale_qty' => 1,
                'max_sale_qty' => 10,
                'is_in_stock' => 0,
                'qty' => 0
            ),
            'website_ids' => array(1),
            'categories' => array(0)
        )
    )
);