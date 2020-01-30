<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\DataType;

use OxidEsales\GraphQL\Base\DataType\BoolFilter;

abstract class FilterList
{
    /** @var ?BoolFilter */
    protected $active = null;

    abstract public function getFilters(): array;

    public function withActiveFilter(BoolFilter $active): self
    {
        $filterList = clone $this;
        $filterList->active = $active;
        return $filterList;
    }

    public function getActive(): ?BoolFilter
    {
        return $this->active;
    }
}