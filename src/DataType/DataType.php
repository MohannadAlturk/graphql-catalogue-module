<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\DataType;

use OxidEsales\Eshop\Core\Model\BaseModel;

interface DataType
{
    /**
     * @return BaseModel
     */
    public function getModel();

    /**
     * @return class-string
     */
    public static function getModelClass(): string;
}