<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\DataType;

use OxidEsales\GraphQL\Base\DataType\IDFilter;
use OxidEsales\GraphQL\Base\DataType\StringFilter;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Service\Repository;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use OxidEsales\GraphQL\Catalogue\DataType\Product as ProductDataType;
use OxidEsales\GraphQL\Base\DataType\BoolFilter;

/**
 * @ExtendType(class=Manufacturer::class)
 */
class ManufacturerRelationService
{
    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @Field()
     */
    public function getSeo(Manufacturer $manufacturer): Seo
    {
        $seo = new Seo($manufacturer->getEshopModel());

        return $seo;
    }

    /**
     * @Field()
     *
     * @return ProductDataType[]
     */
    public function getProducts(Manufacturer $manufacturer, int $offset = null, int $limit = null): array
    {
        $filter = new ProductFilterList(null, null, new IDFilter($manufacturer->getId()), null, new BoolFilter(true));

        $products = $this->repository->getByFilter(
            $filter,
            ProductDataType::class,
            $offset,
            $limit
        );

        return $products;
    }
}
