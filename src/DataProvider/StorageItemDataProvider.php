<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\StorageItemResource\ApiResource\StorageItemResource;
use WhiteDigital\EntityResourceMapper\DataProvider\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;

final class StorageItemDataProvider extends AbstractDataProvider
{
    /**
     * @throws ReflectionException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            throw new NotFoundHttpException($this->translator->trans('resource_not_found', domain: 'StorageItemResource'));
        }

        return $this->getItem($operation, $uriVariables['id'], $context);
    }

    /**
     * @throws ExceptionInterface
     * @throws ResourceClassNotFoundException
     * @throws ReflectionException
     */
    protected function createResource(BaseEntity $entity, array $context): StorageItemResource
    {
        return StorageItemResource::create($entity, $context);
    }
}
