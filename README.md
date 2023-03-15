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
