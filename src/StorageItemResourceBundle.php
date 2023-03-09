<?php declare(strict_types = 1);

namespace WhiteDigital\StorageItemResource;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vich\UploaderBundle\Naming\SmartUniqueNamer;
use WhiteDigital\EntityResourceMapper\DependencyInjection\Traits\DefineApiPlatformMappings;
use WhiteDigital\EntityResourceMapper\DependencyInjection\Traits\DefineOrmMappings;

use function array_merge_recursive;

class StorageItemResourceBundle extends AbstractBundle
{
    use DefineApiPlatformMappings;
    use DefineOrmMappings;

    public const VICH_MAPPING = 'wd_storage_item_media_object';

    private const MAPPINGS = [
        'type' => 'attribute',
        'dir' => __DIR__ . '/Entity',
        'alias' => 'StorageItemResource',
        'prefix' => 'WhiteDigital\StorageItemResource\Entity',
        'is_bundle' => false,
        'mapping' => true,
    ];

    private const PATHS = [
        '%kernel.project_dir%/vendor/whitedigital-eu/storage-item-resource/src/ApiResource',
    ];

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('entity_manager')->defaultValue('default')->end()
                ->end();
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $apiResource = array_merge_recursive(...$builder->getExtensionConfig('api_resource'));
        $audit = array_merge_recursive(...$builder->getExtensionConfig('audit'));

        $this->addDoctrineConfig($container, $apiResource['entity_manager'] ?? 'default', 'StorageItemResource', self::MAPPINGS);
        $this->addApiPlatformPaths($container, self::PATHS);

        if (null !== ($audit['audit_entity_manager'] ?? null)) {
            $this->addDoctrineConfig($container, $audit['audit_entity_manager'], 'StorageItemResource', self::MAPPINGS);
        }

        $container->extension('vich_uploader', [
            'db_driver' => 'orm',
            'mappings' => [
                self::VICH_MAPPING => [
                    'uri_prefix' => '/storage',
                    'upload_destination' => '%kernel.project_dir%/public/storage',
                    'inject_on_load' => false,
                    'namer' => SmartUniqueNamer::class,
                ],
            ],
        ]);
    }
}
