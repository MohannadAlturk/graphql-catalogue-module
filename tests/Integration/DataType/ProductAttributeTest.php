<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Tests\Integration\DataType;

use OxidEsales\Eshop\Application\Model\Article as EshopArticle;
use OxidEsales\GraphQL\Base\Tests\Integration\TestCase;
use OxidEsales\GraphQL\Catalogue\DataType\Product;
use OxidEsales\GraphQL\Catalogue\DataType\ProductAttribute;
use OxidEsales\GraphQL\Catalogue\DataType\ProductRelationService;

/**
 * @covers \OxidEsales\GraphQL\Catalogue\DataType\ProductAttribute
 * @covers \OxidEsales\GraphQL\Catalogue\DataType\ProductRelationService
 */
class ProductAttributeTest extends TestCase
{
    public function testGetProductAttributesTypeAndCount()
    {
        $article = oxNew(EshopArticle::class);
        $article->load('096e38032896a847682651d565966c45');
        $product = new Product(
            $article
        );

        $productRelation = new ProductRelationService(
            $this->createPartialMock(\OxidEsales\GraphQL\Catalogue\Service\Repository::class, [])
        );
        $productAttributes = $productRelation->getAttributes($product);

        $this->assertCount(2, $productAttributes);
        foreach ($productAttributes as $attribute) {
            $this->assertInstanceOf(ProductAttribute::class, $attribute);
        }
    }

    /**
     * @param $languageId
     * @param $key
     * @param $title
     * @param $value
     *
     * @dataProvider getProductAttributesContentDataProvider
     */
    public function testGetProductAttributesContent($languageId, $key, $title, $value)
    {
        $this->setGETRequestParameter(
            'lang',
            $languageId
        );

        $article = oxNew(EshopArticle::class);
        $article->load('096e38032896a847682651d565966c45');
        $product = new Product(
            $article
        );

        $productRelation = new ProductRelationService(
            $this->createPartialMock(\OxidEsales\GraphQL\Catalogue\Service\Repository::class, [])
        );
        $productAttributes = $productRelation->getAttributes($product);

        $this->assertEquals($title, $productAttributes[$key]->getTitle());
        $this->assertEquals($value, $productAttributes[$key]->getValue());
    }

    public function getProductAttributesContentDataProvider()
    {
        return [
            [
                '0',
                '9438ac75bac3e344628b14bf7ed82c15',
                'Farbe',
                'Schwarz',
            ],
            [
                '1',
                '9438ac75bac3e344628b14bf7ed82c15',
                'Color',
                'Black',
            ],
            [
                '1',
                '943d32fd45d6eba3e5c8cce511cc0e74',
                'Size',
                'W 34/L 34',
            ]
        ];
    }
}
