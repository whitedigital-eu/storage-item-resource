<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\EntityResourceMapper\DataProcessor\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\StorageItemResource\Api\Resource\StorageItemResource;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

final class StorageItemDataProcessor extends AbstractDataProcessor
{
    public function getEntityClass(): string
    {
        return StorageItem::class;
    }

    protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): StorageItem
    {
        return StorageItem::create($resource, $context, $existingEntity);
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): StorageItemResource
    {
        return StorageItemResource::create($entity, $context);
    }
}
