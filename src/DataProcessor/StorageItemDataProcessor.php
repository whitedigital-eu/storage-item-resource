<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\DataProcessor;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

use function preg_match;

final class StorageItemDataProcessor implements ProcessorInterface
{
    public function __construct(
        protected readonly AuthorizationService $authorizationService,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($operation instanceof DeleteOperationInterface) {
            $this->remove($data);
        }
    }

    protected function remove(BaseResource $resource): void
    {
        $this->authorizationService->authorizeSingleObject($resource, AuthorizationService::ITEM_DELETE);
        $entity = $this->entityManager->getRepository(StorageItem::class)->find($resource->id);
        if (null !== $entity) {
            $this->removeWithFkCheck($entity);
        }
    }

    protected function removeWithFkCheck(BaseEntity $entity): void
    {
        $this->entityManager->remove($entity);

        try {
            $this->entityManager->flush();
        } catch (Exception $exception) {
            preg_match('/DETAIL: (.*)/', $exception->getMessage(), $matches);
            throw new AccessDeniedHttpException($this->translator->trans('unable_to_delete_record', ['%detail%' => $matches[1]], domain: 'StorageItemResource'), $exception);
        }
    }
}
