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

    private const API_RESOURCE_PATH = '%kernel.project_dir%/vendor/whitedigital-eu/storage-item-resource/src/Api/Resource';

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
                    ->scalarNode('uri_prefix')->defaultValue('/storage')->end()
                    ->scalarNode('upload_destination')->defaultValue('%kernel.project_dir%/public/storage')->end()
                    ->scalarNode('custom_api_resource_path')->defaultNull()->end()
                ->end();
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $extensionConfig = array_merge_recursive(...$builder->getExtensionConfig('storage_item_resource'));
        $apiResourceExtensionConfig = array_merge_recursive(...$builder->getExtensionConfig('api_resource'));
        $auditExtensionConfig = array_merge_recursive(...$builder->getExtensionConfig('audit'));

        $this->addDoctrineConfig($container, $apiResourceExtensionConfig['entity_manager'] ?? 'default', 'StorageItemResource', self::MAPPINGS);

        if (null !== ($auditExtensionConfig['audit_entity_manager'] ?? null)) {
            $this->addDoctrineConfig($container, $auditExtensionConfig['audit_entity_manager'], 'StorageItemResource', self::MAPPINGS);
        }

        $container->extension('vich_uploader', [
            'db_driver' => 'orm',
            'mappings' => [
                self::VICH_MAPPING => [
                    'uri_prefix' => $apiResourceExtensionConfig['uri_prefix'] ?? '/storage',
                    'upload_destination' => $apiResourceExtensionConfig['upload_destination'] ?? '%kernel.project_dir%/public/storage',
                    'inject_on_load' => false,
                    'namer' => SmartUniqueNamer::class,
                ],
            ],
        ]);

        $this->configureApiPlatformExtension($container, $extensionConfig);
    }

    private function configureApiPlatformExtension(ContainerConfigurator $container, array $extensionConfig): void
    {
        if (!array_key_exists('custom_api_resource_path', $extensionConfig)) {
            $this->addApiPlatformPaths($container, [self::API_RESOURCE_PATH]);
        } elseif (!empty($extensionConfig['custom_api_resource_path'])) {
            $this->addApiPlatformPaths($container, [$extensionConfig['custom_api_resource_path']]);
        }
    }
}
