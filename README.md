# Storage item resource

### System Requirements
PHP 8.1+  
Symfony 6.2+

### Installation
The recommended way to install is via Composer:

```shell
composer require whitedigital-eu/storage-item-resource
```

With the help of `vich/uploader-bundle` this package enables file upload when used with api platform.
After this, you need to update your database schema to use StorageItem entity.  
If using migrations:
```shell
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```
If by schema update:
```shell
bin/console doctrine:schema:update --force
``` 
This will enable new `StorageItem` api resource with `/api/storage_items` iri. If you want different iri, see
https://github.com/whitedigital-eu/entity-resource-mapper#extended-api-resource how to override it.

**Overriding default api resource (and therefore api endpoints)**

By default, StorageItem resource is based on `StorageItemResource`  
If you wish not to use this resource and not expose the api endpoints it provides, just set a custom api resource path
with a configuration value. If you set it as `null`, api platform will not register api resource located within this
package.

```yaml
storage_item_resource:
    custom_api_resource_path: '%kernel.project_dir%/src/MyCustomPath'
#    custom_api_resource_path: null
```

```php
use Symfony\Config\StorageItemResourceConfig;
return static function (StorageItemResourceConfig $config): void {
    $config->customApiResourcePath('%kernel.project_dir%/src/MyCustomPath')
    // or  ->customApiResourcePath(null);
};
```
After overriding default api resource, do not forget to update ClassMapperConfigurator configuration that is used for
resource <-> entity mapping in `whitedigital-eu/entity-resource-mapper-bundle`
```php
use App\ApiResource\Admin\StorageItemResource;
use WhiteDigital\StorageItem\Entity\StorageItem;
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapper;
use WhiteDigital\EntityResourceMapper\Mapper\ClassMapperConfiguratorInterface;
final class ClassMapperConfigurator implements ClassMapperConfiguratorInterface
{
    public function __invoke(ClassMapper $classMapper): void
    {
        $classMapper->registerMapping(StorageItemResource::class, StorageItem::class);
    }
}
```
---