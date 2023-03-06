<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
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

#[
    ApiResource(
        shortName: 'StorageItem',
        types: ['https://schema.org/MediaObject', ],
        operations: [
            new Post(
                controller: CreateStorageObjectController::class,
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
            new Get(
                normalizationContext: ['groups' => [self::ITEM, ], ],
            ),
            new Delete(),
        ],
        normalizationContext: ['groups' => [self::ITEM, ], ],
        provider: StorageItemDataProvider::class,
        processor: StorageItemDataProcessor::class,
    )
]
#[Vich\Uploadable]
#[Mapping(StorageItem::class)]
class StorageItemResource extends BaseResource
{
    public const ITEM = self::PREFIX . 'item';
    public const WRITE = self::PREFIX . 'write';

    public const PREFIX = 'storage_item:';

    #[ApiProperty(identifier: true)]
    #[Groups([self::ITEM, ])]
    public mixed $id = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl', ])]
    #[Groups([self::ITEM, ])]
    public ?string $contentUrl = null;

    #[Assert\NotNull(groups: [self::WRITE, ])]
    #[Assert\File(groups: [self::WRITE, ])]
    #[Vich\UploadableField(
        mapping: 'wd_sir_media_object',
        fileNameProperty: 'filePath',
        size: 'size',
        mimeType: 'mimeType',
        originalName: 'originalName',
        dimensions: 'dimensions',
    )]
    public ?File $file = null;

    #[Groups([self::ITEM, ])]
    public ?string $filePath = null;

    #[Groups([self::ITEM, ])]
    public ?int $size = null;

    #[Groups([self::ITEM, ])]
    public ?string $mimeType = null;

    #[Groups([self::ITEM, ])]
    public ?string $originalName = null;

    #[Groups([self::ITEM, ])]
    public ?array $dimensions = null;

    #[Groups([self::ITEM, ])]
    public ?string $title = null;

    #[Groups([self::ITEM, ])]
    public ?DateTimeImmutable $createdAt = null;

    #[Groups([self::ITEM, ])]
    public ?DateTimeImmutable $updatedAt = null;
}
