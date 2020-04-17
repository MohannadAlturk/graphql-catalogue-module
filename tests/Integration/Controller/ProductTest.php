<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Tests\Integration\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\GraphQL\Catalogue\Tests\Integration\TokenTestCase;

final class ProductTest extends TokenTestCase
{
    private const ACTIVE_PRODUCT = '058e613db53d782adfc9f2ccb43c45fe';
    private const INACTIVE_PRODUCT  = '09602cddb5af0aba745293d08ae6bcf6';
    private const ACTIVE_PRODUCT_WITH_ACCESSORIES = '05848170643ab0deb9914566391c0c63';
    private const ACTIVE_PRODUCT_WITH_UNITNAME = 'f33d5bcc7135908fd36fc736c643aa1c';
    private const ACTIVE_PRODUCT_WITH_SELECTION_LISTS = '058de8224773a1d5fd54d523f0c823e0';
    private const ACTIVE_PRODUCT_WITH_RESTOCK_DATE = 'f4fe754e1692b9f79f2a7b1a01bb8dee';
    private const ACTIVE_PRODUCT_WITH_SCALE_PRICES = 'dc53d3c0ca2ae7c38bf51f3410da0bf8';

    public function testGetSingleActiveProduct()
    {
        $result = $this->query('query {
            product(id: "' . self::ACTIVE_PRODUCT . '") {
                dimensions {
                    length
                    width
                    height
                    weight
                }
                price {
                    price
                    vat
                    vatValue
                    nettoPriceMode
                    currency {
                        id
                        name
                        rate
                        sign
                    }
                }
                listPrice {
                    price
                    vat
                    vatValue
                    nettoPriceMode
                }
                stock {
                    stock
                    stockStatus
                    restockDate
                }
                imageGallery {
                    images {
                        image
                        icon
                        zoom
                    }
                    icon
                    thumb
                }
                rating {
                    rating
                    count
                    ratings {
                        rating
                    }
                }
                scalePrices {
                    absoluteScalePrice
                    absolutePrice
                    discount
                    amountFrom
                    amountTo
                }
                unit {
                    price {
                        price
                        vat
                        vatValue
                        nettoPriceMode
                    }
                    name
                }
                seo {
                    description
                    keywords
                    url
                }
                crossSelling {
                    id
                }
                accessories {
                    id
                }
                deliveryTime {
                    minDeliveryTime
                    maxDeliveryTime
                    deliveryTimeUnit
                }
                attributes {
                    attribute {
                        title
                    }
                    value
                }
                selectionLists {
                    title
                    fields {
                        value
                    }
                }
                id
                active
                sku
                ean
                manufacturerEan
                manufacturer {
                    id
                }
                vendor {
                    id
                }
                category {
                    id
                }
                bundleProduct {
                    id
                }
                mpn
                title
                shortDescription
                longDescription
                vat
                insert
                freeShipping
                timestamp
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $product = $result['body']['data']['product'];

        $this->assertSame(self::ACTIVE_PRODUCT, $product['id']);

        $dimensions = $product['dimensions'];
        $this->assertSame(0.0, $dimensions['length']);
        $this->assertSame(0.0, $dimensions['width']);
        $this->assertSame(0.0, $dimensions['height']);
        $this->assertSame(0.0, $dimensions['weight']);

        $deliveryTime = $product['deliveryTime'];
        if (\version_compare(\PHPUnit\Runner\Version::id(), '7.0.0') >= 0) {
            $this->assertIsInt($deliveryTime['minDeliveryTime']);
            $this->assertIsInt($deliveryTime['maxDeliveryTime']);
        } else {
            $this->assertTrue(is_int($deliveryTime['minDeliveryTime']), 'minDeliveryTime must be of type integer');
            $this->assertTrue(is_int($deliveryTime['maxDeliveryTime']), 'maxDeliveryTime must be of type integer');
        }
        $this->assertGreaterThan(0, $deliveryTime['minDeliveryTime']);
        $this->assertGreaterThan(0, $deliveryTime['maxDeliveryTime']);
        $this->assertContains(
            $deliveryTime['deliveryTimeUnit'],
            ['DAY','WEEK'],
            'deliveryTimeUnit must be one of DAY, WEEK, but is not'
        );

        $price = $product['price'];
        $this->assertSame(359.0, $price['price']);
        $this->assertSame(19.0, $price['vat']);
        $this->assertSame(57.32, $price['vatValue']);
        $this->assertFalse($price['nettoPriceMode']);

        $this->assertNull($product['manufacturer']);
        $this->assertNull($product['vendor']);
        $this->assertNull($product['bundle']);

        $this->assertSame('0f40c6a077b68c21f164767c4a903fd2', $product['category']['id']);

        $currency = $price['currency'];
        $expectedCurrency = Registry::getConfig()->getActShopCurrencyObject();
        $this->assertSame($expectedCurrency->id, $currency['id']);
        $this->assertSame($expectedCurrency->name, $currency['name']);
        $this->assertSame($expectedCurrency->rate, $currency['rate']);
        $this->assertSame($expectedCurrency->sign, $currency['sign']);

        $listPrice = $product['listPrice'];
        $this->assertSame(399.0, $listPrice['price']);
        $this->assertSame(19.0, $listPrice['vat']);
        $this->assertSame(63.71, $listPrice['vatValue']);
        $this->assertFalse($listPrice['nettoPriceMode']);

        $stock = $product['stock'];
        $this->assertSame(16.0, $stock['stock']);
        $this->assertSame(0, $stock['stockStatus']);
        $this->assertNull($stock['restockDate']);

        $this->assertCount(
            3,
            $product['crossSelling']
        );

        $this->assertCount(
            0,
            $product['accessories']
        );

        $imageGallery = $product['imageGallery'];
        $images = $imageGallery['images'][0];
        $this->assertRegExp(
            '@https?://.*/out/pictures/generated/product/1/540_340_75/obrien_decade_ct_boot_2010_1.jpg@',
            $images['image']
        );
        $this->assertRegExp(
            '@https?://.*/out/pictures/generated/product/1/87_87_75/obrien_decade_ct_boot_2010_1.jpg@',
            $images['icon']
        );
        $this->assertRegExp(
            '@https?://.*/out/pictures/generated/product/1/665_665_75/obrien_decade_ct_boot_2010_1.jpg@',
            $images['zoom']
        );
        $this->assertRegExp(
            '@https?://.*/out/pictures/generated/product/1/87_87_75/obrien_decade_ct_boot_2010_1.jpg@',
            $imageGallery['icon']
        );
        $this->assertRegExp(
            '@https?://.*/out/pictures/generated/product/1/390_245_75/obrien_decade_ct_boot_2010_1.jpg@',
            $imageGallery['thumb']
        );

        $rating = $product['rating'];
        $this->assertSame(0.0, $rating['rating']);
        $this->assertSame(0, $rating['count']);
        $this->assertCount(3, $rating['ratings']);

        $this->assertSame(
            [],
            $product['scalePrices']
        );

        $this->assertNull($product['unit']);
        $this->assertTrue($product['active']);
        $this->assertSame('2401', $product['sku']);
        $this->assertSame('', $product['ean']);
        $this->assertSame('', $product['manufacturerEan']);
        $this->assertSame([], $product['attributes']);
        $this->assertSame([], $product['selectionLists']);
        $this->assertSame('', $product['mpn']);
        $this->assertSame('Bindung O&#039;BRIEN DECADE CT 2010', $product['title']);
        $this->assertSame('Geringes Gewicht, beste Performance!', $product['shortDescription']);
        $this->assertSame(
            "<p>\r\n<div class=\"product_title_big\">\r\n<h2>O'Brien Decade CT Boot 2010</h2></div>\r\n" .
            "    Die Decade Pro Bindung ist nicht nur\r\n Close-Toe Boots mit hammer Style," .
            " sondern es war das Ziel den Komfort\r\nvon normalen Boots in eine Wakeboard-Bindung einzubringen," .
            " dabei\r\nleichtgewichtig zu bleiben, und damit perfekte Performance auf dem\r\nWasser zu ermöglichen." .
            "<br />\r\n<br />\r\n  Die Wakeboard-Bindung ist in der Größe gleich bleibend, gibt also im\r\n" .
            "Laufe der Zeit nicht nach und sitzt somit wie ein perfekter Schuh. Ein\r\n" .
            "ergonomisches und stoßabsorbierendes Fussbett sorgt für weiche\r\nLandungen.</p>\r\n<p> </p>",
            $product['longDescription']
        );
        $this->assertSame(19.0, $product['vat']);
        $this->assertSame('2010-12-06T00:00:00+01:00', $product['insert']);
        $this->assertFalse($product['freeShipping']);
        $this->assertRegExp(
            '@https?://.*/Wakeboarding/Bindungen/Bindung-O-BRIEN-DECADE-CT-2010.html@',
            $product['seo']['url']
        );
        $this->assertEquals('german product seo description', $product['seo']['description']);
        $this->assertEquals('german product seo keywords', $product['seo']['keywords']);
    }

    public function testGetAccessoriesRelation()
    {
        $result = $this->query('query {
            product (id: "' . self::ACTIVE_PRODUCT_WITH_ACCESSORIES . '") {
                id
                accessories {
                    id
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $product = $result['body']['data']['product'];

        $this->assertCount(
            2,
            $product['accessories']
        );

        $this->assertSame(
            [
                [
                    'id' => 'adcb9deae73557006a8ac748f45288b4'
                ], [
                    'id' => 'd86236918e1533cccb679208628eda32'
                ]
            ],
            $product['accessories']
        );
    }

    public function testGetSingleInactiveProductWithoutToken()
    {
        $result = $this->query('query {
            product (id: "' . self::INACTIVE_PRODUCT . '") {
                id
                active
            }
        }');

        $this->assertResponseStatus(
            401,
            $result
        );
    }

    public function testGetSingleInactiveProductWithToken()
    {
        $this->prepareToken();

        $result = $this->query('query {
            product (id: "' . self::INACTIVE_PRODUCT . '") {
                id
                active
            }
        }');

        $this->assertEquals(200, $result['status']);
        $this->assertEquals(
            [
                'id' => self::INACTIVE_PRODUCT,
                'active' => false
            ],
            $result['body']['data']['product']
        );
    }

    public function testGetSingleNonExistingProduct()
    {
        $result = $this->query('query {
            product (id: "DOES-NOT-EXIST") {
                id
            }
        }');

        $this->assertEquals(404, $result['status']);
    }

    /**
     * @dataProvider productWithATtributesProvider
     */
    public function testGetProductAttributesRelation(string $productId, array $expectedAttributes): void
    {
        $result = $this->query('
            query{
                product(id: "' . $productId . '" ){
                    attributes {
                        value
                        attribute {
                          title
                        }
                    }
                }
            }
        ');

        $this->assertEquals(200, $result['status']);

        $attributes = $result['body']['data']['product']['attributes'];

        $this->assertArraySameNonAssociative($expectedAttributes, $attributes);
    }

    public function productWithAttributesProvider(): array
    {
        return [
            [
                'product' => 'b56369b1fc9d7b97f9c5fc343b349ece',
                'expectedAttributes' => [
                    [
                        'value' => 'Kite, Backpack, Reparaturset',
                        'attribute' => ['title' => 'Lieferumfang'],
                    ],
                    [
                        'value' => 'Allround',
                        'attribute' => ['title' => 'Einsatzbereich'],
                    ],
                ],
            ],
            [
                'product' => 'f4f0cb3606e231c3fdb34fcaee2d6d04',
                'expectedAttributes' => [
                    [
                        'value' => 'Allround',
                        'attribute' => ['title' => 'Einsatzbereich'],
                    ],
                    [
                        'value' => 'Kite, Tasche, CPR Control System, Pumpe',
                        'attribute' => ['title' => 'Lieferumfang'],
                    ],
                ],
            ],
            [
                'product' => '058de8224773a1d5fd54d523f0c823e0',
                'expectedAttributes' => [],
            ],
        ];
    }

    public function testGetSelectionLists()
    {
        $result = $this->query('query {
            product (id: "' . self::ACTIVE_PRODUCT_WITH_SELECTION_LISTS . '") {
                id
                selectionLists {
                    title
                    fields {
                        value
                    }
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $product = $result['body']['data']['product'];

        $this->assertCount(
            1,
            $product['selectionLists']
        );

        $this->assertSame(
            [
                'title' => 'test selection list [DE] šÄßüл',
                'fields' => [
                    [
                        'value' => 'selvar1 [DE]',
                    ],
                    [
                        'value' => 'selvar2 [DE]',
                    ],
                    [
                        'value' => 'selvar3 [DE]',
                    ],
                    [
                        'value' => 'selvar4 [DE]',
                    ],
                ],
            ],
            $product['selectionLists'][0]
        );
    }

    private function assertArraySameNonAssociative(array $expected, array $actual): void
    {
        $this->assertSame(sort($expected), sort($actual));
    }

    /**
     * @dataProvider getReviewsConfigDataProvider
     */
    public function testGetReviews($configValue, $expectedIds)
    {
        $this->getConfig()->setConfigParam('blGBModerate', $configValue);

        $result = $this->query('query {
            product (id: "' . self::ACTIVE_PRODUCT . '") {
                id
                reviews {
                    id
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $product = $result['body']['data']['product'];

        $this->assertSame(
            $expectedIds,
            $product['reviews']
        );
    }

    public function getReviewsConfigDataProvider()
    {
        return [
            [
                true,
                [
                    ['id' => '_test_real_product_1'],
                    ['id' => '_test_real_product_2']
                ]
            ],
            [
                false,
                [
                    ['id' => '_test_real_product_1'],
                    ['id' => '_test_real_product_2'],
                    ['id' => '_test_real_product_inactive']
                ]
            ]
        ];
    }

    public function testGetNoReviews()
    {
        $result = $this->query('query {
            product (id: "' . self::ACTIVE_PRODUCT_WITH_ACCESSORIES . '") {
                id
                reviews {
                    id
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $this->assertCount(
            0,
            $result['body']['data']['product']['reviews']
        );
    }

    public function testGetUnitNameAndPrice()
    {
         $result = $this->query('query {
            product (id: "' . self::ACTIVE_PRODUCT_WITH_UNITNAME . '") {
                id
                unit {
                    name
                    price {
                        price
                    }
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $this->assertSame('g', $result['body']['data']['product']['unit']['name']);
        $this->assertSame(0.42, $result['body']['data']['product']['unit']['price']['price']);
    }

    public function testGetRestockDate()
    {
         $result = $this->query('query {
            product (id: "' . self::ACTIVE_PRODUCT_WITH_RESTOCK_DATE . '") {
                id
                stock {
                    restockDate
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $this->assertSame('2999-12-31T00:00:00+01:00', $result['body']['data']['product']['stock']['restockDate']);
    }

    public function testGetScalePrices()
    {
         $result = $this->query('query {
            product (id: "' . self::ACTIVE_PRODUCT_WITH_SCALE_PRICES . '") {
                id
                scalePrices {
                    absolutePrice
                    amountFrom
                    amountTo
                }
            }
        }');

        $this->assertResponseStatus(
            200,
            $result
        );

        $this->assertCount(
            0,
            $result['body']['data']['product']['scalePrices']
        );
    }
}
