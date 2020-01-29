<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Service;

use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\DataType\FilterList;
use OxidEsales\GraphQL\Catalogue\DataType\DataType;

class Repository
{
    /** @var QueryBuilderFactoryInterface $queryBuilderFactory */
    private $queryBuilderFactory;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * @template T
     * @template M
     * @param class-string<T> $dataType
     * @param class-string<M> $model
     * @return T
     */
    public function getById(
        string $id,
        string $dataType,
        string $model
    ) {
        /** @var BaseModel */
        $model = oxNew($model);
        if (!$model->load($id)) {
            throw new NotFound($id);
        }
        return new $dataType($model);
    }

    /**
     * @template T
     * @template M
     * @param class-string<T> $dataType
     * @param class-string<M> $model
     * @return T[]
     */
    public function getByFilter(
        FilterList $filter,
        string $dataType,
        string $model
    ): array {
        $models = [];
        /** @var BaseModel */
        $model = oxNew($model);
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->select('*')
                     ->from($model->getViewName())
                     ->orderBy('oxid');
        $filters = array_filter($filter->getFilters());
        if (isset($filters['oxactive']) && $filters['oxactive']->equals() === true) {
            unset($filters['oxactive']);
            $queryBuilder->andWhere($model->getSqlActiveSnippet());
        }
        foreach ($filters as $field => $fieldFilter) {
            $fieldFilter->addToQuery($queryBuilder, $field);
        }
        $result = $queryBuilder->execute();
        if (!$result instanceof \Doctrine\DBAL\Driver\Statement) {
            return $models;
        }
        foreach ($result as $row) {
            $newModel = clone $model;
            $newModel->assign($row);
            $models[] = new $dataType($newModel);
        }
        return $models;
    }

    public function save(DataType $dataType): DataType
    {
        $model = $dataType->getModel();
        if (!$model->save()) {
            throw new \Exception();
        }
        // reload model
        $model->load($model->getId());
        $class = get_class($dataType);
        return new $class($model);
    }
}
