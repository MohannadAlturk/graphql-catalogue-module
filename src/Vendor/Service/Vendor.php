<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Vendor\Service;

use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use OxidEsales\GraphQL\Catalogue\Vendor\DataType\Vendor as VendorDataType;
use OxidEsales\GraphQL\Catalogue\Vendor\DataType\VendorFilterList;
use OxidEsales\GraphQL\Catalogue\Vendor\Exception\VendorNotFound;

final class Vendor
{
    /** @var Repository */
    private $repository;

    /** @var Authorization */
    private $authorizationService;

    public function __construct(
        Repository $repository,
        Authorization $authorizationService
    ) {
        $this->repository           = $repository;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @throws VendorNotFound
     * @throws InvalidLogin
     */
    public function vendor(string $id): VendorDataType
    {
        try {
            $vendor = $this->repository->getById(
                $id,
                VendorDataType::class
            );
        } catch (NotFound $e) {
            throw VendorNotFound::byId($id);
        }

        if ($vendor->isActive()) {
            return $vendor;
        }

        if ($this->authorizationService->isAllowed('VIEW_INACTIVE_VENDOR')) {
            return $vendor;
        }

        throw new InvalidLogin('Unauthorized');
    }

    /**
     * @return VendorDataType[]
     */
    public function vendors(VendorFilterList $filter): array
    {
        // In case user has VIEW_INACTIVE_VENDOR permissions
        // return all vendors including inactive
        if ($this->authorizationService->isAllowed('VIEW_INACTIVE_VENDOR')) {
            $filter = $filter->withActiveFilter(null);
        }

        return $this->repository->getByFilter(
            $filter,
            VendorDataType::class
        );
    }
}