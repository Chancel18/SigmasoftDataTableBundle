# Installation Guide - SigmasoftDataTableBundle

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or 7.0
- Composer

## Installation Steps

### 1. Install the Bundle

```bash
composer require sigmasoft/datatable-bundle
```

### 2. Enable the Bundle (if not using Symfony Flex)

If you're not using Symfony Flex, register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    Sigmasoft\DataTableBundle\SigmasoftDataTableBundle::class => ['all' => true],
];
```

### 3. Configure the Bundle

Create a configuration file `config/packages/sigmasoft_data_table.yaml`:

```yaml
sigmasoft_data_table:
    defaults:
        items_per_page: 10
        enable_search: true
        enable_export: true
        export_formats: ['csv', 'excel']
```

### 4. Update Composer Autoload

After installation, update the autoloader:

```bash
composer dump-autoload
```

### 5. Clear Cache

Clear the Symfony cache:

```bash
php bin/console cache:clear
```

### 6. Install Assets (if using Symfony UX)

```bash
php bin/console assets:install
```

## Verify Installation

### Check if the bundle is loaded:

```bash
php bin/console config:dump-reference sigmasoft_data_table
```

### Check if Maker command is available:

```bash
php bin/console list make
```

You should see `make:datatable` in the list.

### Check services:

```bash
php bin/console debug:container sigmasoft
```

## Troubleshooting

### Maker command not showing

1. Make sure you have `symfony/maker-bundle` installed:
   ```bash
   composer require symfony/maker-bundle --dev
   ```

2. Clear the cache:
   ```bash
   php bin/console cache:clear
   ```

### Service not found errors

1. Check that the bundle is registered
2. Update autoloader: `composer dump-autoload`
3. Clear cache: `php bin/console cache:clear`

### Template not found errors

Make sure the template path is correct in your configuration:

```yaml
sigmasoft_data_table:
    templates:
        datatable: '@SigmasoftDataTable/datatable.html.twig'
```

## Next Steps

1. Create your first DataTable using the Maker:
   ```bash
   php bin/console make:datatable User
   ```

2. Check the documentation for advanced usage:
   - [Basic Usage](README.md#usage)
   - [Custom Renderers](Documentation/CUSTOM_RENDERERS.md)
   - [Examples](https://chancel18.github.io/SigmasoftDataTableBundle/)