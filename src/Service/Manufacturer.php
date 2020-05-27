<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Service;

use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\DataType\Manufacturer as ManufacturerDataType;
use OxidEsales\GraphQL\Catalogue\DataType\ManufacturerFilterList;
use OxidEsales\GraphQL\Catalogue\Exception\ManufacturerNotFound;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Service\Repository;

final class Manufacturer
{
    /** @var Repository */
    private $repository;

    /** @var Authorization */
    private $authorizationService;

    public function __construct(
        Repository $repository,
        Authorization $authorizationService
    ) {
        $this->repository = $repository;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @throws ManufacturerNotFound
     * @throws InvalidLogin
     */
    public function manufacturer(string $id): ManufacturerDataType
    {
        try {
            /** @var ManufacturerDataType $manufacturer */
            $manufacturer = $this->repository->getById(
                $id,
                ManufacturerDataType::class
            );
        } catch (NotFound $e) {
            throw ManufacturerNotFound::byId($id);
        }

        if ($manufacturer->isActive()) {
            return $manufacturer;
        }

        if ($this->authorizationService->isAllowed('VIEW_INACTIVE_MANUFACTURER')) {
            return $manufacturer;
        }

        throw new InvalidLogin("Unauthorized");
    }

    /**
     * @return ManufacturerDataType[]
     */
    public function manufacturers(ManufacturerFilterList $filter): array
    {
        // In case user has VIEW_INACTIVE_MANUFACTURER permissions
        // return all manufacturers including inactive ones
        if ($this->authorizationService->isAllowed('VIEW_INACTIVE_MANUFACTURER')) {
            $filter = $filter->withActiveFilter(null);
        }

        $manufacturers = $this->repository->getByFilter(
            $filter,
            ManufacturerDataType::class
        );

        return $manufacturers;
    }
}
