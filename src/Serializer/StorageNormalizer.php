<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource\Serializer;

use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use WhiteDigital\StorageItemResource\ApiResource\StorageItemResource;

final class StorageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'WD_SIR_STORAGE_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly StorageInterface $storage)
    {
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof StorageItemResource;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): float|array|ArrayObject|bool|int|string|null
    {
        $context[self::ALREADY_CALLED] = true;

        $object->contentUrl = $this->storage->resolveUri($object, 'file');

        return $this->normalizer->normalize($object, $format, $context);
    }
}
