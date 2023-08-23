<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\EntityResourceMapper\DataProvider\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\StorageItemResource\Api\Resource\StorageItemResource;

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

        $resource = $this->getItem($operation, $uriVariables['id'], $context);
        if (null === $resource->title) {
            $resource->title = $resource->originalName;
        }

        return $resource;
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
