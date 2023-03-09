<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\Controller;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;
use WhiteDigital\EntityResourceMapper\Security\Enum\GrantType;
use WhiteDigital\StorageItemResource\ApiResource\StorageItemResource;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

use function array_key_exists;
use function array_merge;

#[AsController]
class CreateStorageObjectController extends AbstractController
{
    public function __construct(
        protected readonly AuthorizationService $authorizationService,
        protected readonly EntityManagerInterface $em,
        protected readonly StorageInterface $vichStorage,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request): StorageItemResource
    {
        if (!$request->files->has($key = 'file')) {
            throw new BadRequestHttpException($this->translator->trans('named_required_parameter_is_missing', ['%parameter%' => 'file'], domain: 'StorageItemResource'));
        }

        $uploadedFile = $request->files->get($key);

        if (!$uploadedFile instanceof UploadedFile) {
            throw new BadRequestHttpException($this->translator->trans('named_required_parameter_is_incorrect', ['%parameter%' => 'file'], domain: 'StorageItemResource'));
        }

        if ($uploadedFile->getError()) {
            throw new BadRequestHttpException($this->translator->trans($uploadedFile->getErrorMessage()));
        }

        $storage = (new StorageItem())->setFile($uploadedFile)->setTitle($request->request->get('title'));

        $this->authorizationService->setAuthorizationOverride(fn () => $this->override(AuthorizationService::COL_POST, StorageItemResource::class));
        $this->authorizationService->authorizeSingleObject($storage, AuthorizationService::COL_POST);

        $this->em->persist($storage);
        $this->em->flush();

        $mediaObject = new StorageItemResource();
        $mediaObject->contentUrl = $this->vichStorage->resolveUri($storage, 'file');
        $mediaObject->createdAt = $storage->getCreatedAt();
        $mediaObject->dimensions = $storage->getDimensions();
        $mediaObject->file = $storage->getFile();
        $mediaObject->filePath = $storage->getFilePath();
        $mediaObject->id = $storage->getId();
        $mediaObject->mimeType = $storage->getMimeType();
        $mediaObject->originalName = $storage->getOriginalName();
        $mediaObject->size = $storage->getSize();
        $mediaObject->title = $storage->getTitle();

        return $mediaObject;
    }

    protected function override(string $operation, string $class): bool
    {
        try {
            $property = (new ReflectionClass($this->authorizationService))->getProperty('resources')->getValue($this->authorizationService);
        } catch (ReflectionException) {
            return false;
        }

        if (isset($property[$class])) {
            $attributes = $property[$class];
        } else {
            return false;
        }

        $allowed = array_merge($attributes[AuthorizationService::ALL] ?? [], $attributes[$operation] ?? []);
        if ([] !== $allowed && array_key_exists(AuthenticatedVoter::PUBLIC_ACCESS, $allowed)) {
            if (GrantType::ALL === $allowed[AuthenticatedVoter::PUBLIC_ACCESS]) {
                return true;
            }

            throw new InvalidConfigurationException('Public access only allowed with "all" grant type');
        }

        return false;
    }
}
