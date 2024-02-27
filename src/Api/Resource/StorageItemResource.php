<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\Api\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use ArrayObject;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\StorageItemResource\Controller\CreateStorageObjectController;
use WhiteDigital\StorageItemResource\DataProcessor\StorageItemDataProcessor;
use WhiteDigital\StorageItemResource\DataProvider\StorageItemDataProvider;
use WhiteDigital\StorageItemResource\Entity\StorageItem;
use WhiteDigital\StorageItemResource\StorageItemResourceBundle;

#[
    ApiResource(
        shortName: 'StorageItem',
        operations: [
            new Post(
                controller: CreateStorageObjectController::class,
                types: ['https://schema.org/MediaObject', ],
                openapi: new Model\Operation(
                    requestBody: new Model\RequestBody(
                        content: new ArrayObject([
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => Type::BUILTIN_TYPE_OBJECT,
                                    'properties' => [
                                        'file' => [
                                            'type' => Type::BUILTIN_TYPE_STRING,
                                            'format' => 'binary',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ),
                validationContext: ['groups' => ['Default', self::WRITE, ], ],
                deserialize: false,
            ),
            new Patch(
                requirements: ['id' => '\d+', ],
                denormalizationContext: ['groups' => [self::PATCH, ], ],
                processor: StorageItemDataProcessor::class,
            ),
            new Get(
                normalizationContext: ['groups' => [self::ITEM, self::READ, ], ],
            ),
            new Delete(
                processor: StorageItemDataProcessor::class,
            ),
        ],
        normalizationContext: ['groups' => [self::ITEM, self::READ, ], ],
        denormalizationContext: ['groups' => [self::WRITE, ], ],
        provider: StorageItemDataProvider::class,
    )
]
#[Vich\Uploadable]
#[Mapping(StorageItem::class)]
class StorageItemResource extends BaseResource
{
    public const ITEM = self::PREFIX . 'item';
    public const READ = self::PREFIX . 'read';
    public const PATCH = self::PREFIX . 'patch';
    public const WRITE = self::PREFIX . 'write';

    public const PREFIX = 'storage_item:';

    #[ApiProperty(identifier: true)]
    #[Groups([self::ITEM, self::READ, ])]
    public mixed $id = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl', ])]
    #[Groups([self::ITEM, self::READ, ])]
    public ?string $contentUrl = null;

    #[Assert\NotNull(groups: [self::WRITE, ])]
    #[Assert\File(groups: [self::WRITE, ])]
    #[Vich\UploadableField(
        mapping: StorageItemResourceBundle::VICH_MAPPING,
        fileNameProperty: 'filePath',
        size: 'size',
        mimeType: 'mimeType',
        originalName: 'originalName',
        dimensions: 'dimensions',
    )]
    public ?File $file = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?string $filePath = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?int $size = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?string $mimeType = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?string $originalName = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?array $dimensions = null;

    #[Groups([self::ITEM, self::WRITE, self::PATCH, self::READ, ])]
    public ?string $title = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?DateTimeImmutable $createdAt = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?DateTimeImmutable $updatedAt = null;

    #[Groups([self::ITEM, self::READ, ])]
    public ?bool $isImage = null;

    #[Groups([self::ITEM, self::WRITE, self::PATCH, self::READ, ])]
    public ?array $data = null;
}
