<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class LocationFilter extends AbstractFilter
{
    /**
     * The distance from a point in meters in which to search for postcodes.
     */
    public const DISTANCE=500;

    /**
     * {@inheritDoc}
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ($property !== 'location') {
          return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $coordinates = explode(',', $value);

        $queryBuilder->andWhere(
            $queryBuilder->expr()->between($rootAlias . '.eastings', $coordinates[0] - self::DISTANCE, $coordinates[0] + self::DISTANCE)
        );
        $queryBuilder->andWhere(
            $queryBuilder->expr()->between($rootAlias . '.northings', $coordinates[1] - self::DISTANCE, $coordinates[1] + self::DISTANCE)
        );

    }

    public function getDescription(string $resourceClass): array
    {
        return [];
    }
}
