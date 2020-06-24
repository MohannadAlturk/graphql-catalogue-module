<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Product\DataType;

use OxidEsales\Eshop\Core\Price as PriceModel;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
final class Price
{
    /** @var PriceModel */
    private $price;

    public function __construct(PriceModel $price)
    {
        $this->price = $price;
    }

    /**
     * @Field()
     */
    public function getPrice(): float
    {
        return $this->price->getPrice();
    }

    /**
     * @Field()
     */
    public function getVat(): float
    {
        return $this->price->getVat();
    }

    /**
     * @Field()
     */
    public function getVatValue(): float
    {
        return $this->price->getVatValue();
    }

    /**
     * @Field()
     */
    public function isNettoPriceMode(): bool
    {
        return $this->price->isNettoMode();
    }
}