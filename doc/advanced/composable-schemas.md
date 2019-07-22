# Splitting up the schema over multiple modules with composable schemas

It is possible to split up the schema into separate parts so that you can enable certain functionality that is for example tied to a particular module only when that module is enabled.

You can find a complete example [here](https://github.com/drupal-graphql/graphql/tree/8.x-4.x/examples) on how this can be done.

If you have a complex system where certain modules are sometimes enabled / disabled and you want to make sure the API matches the functionality provided by those modules and also disable the API's that correspond to those modules when the modules are disabled splitting schemas into chunks is a good idea. This will keep the system organized and easy to read and look at.

## Register a new Schema Extension

A new schema extension can be inserted inside a new module so that it can extend a given schema (in the example bellow the schema `example`) with new fields and types. The `schema` key will tell which schema to attach these types to and the `id` key will be used to tell which files to pick up from when generating the schema.

```php
<?php
namespace Drupal\graphql_examples\Plugin\GraphQL\SchemaExtension;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
/**
 * @SchemaExtension(
 *   id = "example_extension",
 *   name = "Example extension",
 *   description = "A simple extension that adds node related fields.",
 *   schema = "example"
 * )
 */
class ExampleSchemaExtension extends SdlSchemaExtensionPluginBase {
}
```

## Add new types and fields

To add new types and fields to the schema create a file inside `/graphql/example_extension.base.graphqls` (as seen [here](https://github.com/drupal-graphql/graphql/blob/8.x-4.x/examples/graphql/example_extension.base.graphqls)) with the new types :

```
type Page {
  id: Int!
  title: String
}
```

In this case we are creating a new type `Page`.

## Change existing types or fields

Normally a new module when enabled should also change or add more fields to an existing type. In that case create new file `/graphql/example_extension.extension.graphqls` as seen [here](https://github.com/drupal-graphql/graphql/blob/8.x-4.x/examples/graphql/example_extension.extension.graphqls)

```
extend type Query {
  page(id: Int!): Page
}
```

We are adding a new field inside the built-in `Query` type.

## Add the resolvers

We can now add our resolvers to the Extension class created previously so that our new fields actually resolve something :

```php
<?php
namespace Drupal\graphql_examples\Plugin\GraphQL\SchemaExtension;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
/**
 * @SchemaExtension(
 *   id = "example_extension",
 *   name = "Example extension",
 *   description = "A simple extension that adds node related fields.",
 *   schema = "example"
 * )
 */
class ExampleSchemaExtension extends SdlSchemaExtensionPluginBase {
  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();
    $this->addQueryFields($registry, $builder);
    $this->addPageFields($registry, $builder);
  }
  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addPageFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Page', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );
    $registry->addFieldResolver('Page', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
        $builder->produce('uppercase')
          ->map('string', $builder->fromParent())
      )
    );
  }
  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addQueryFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'page',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['page']))
        ->map('id', $builder->fromArgument('id'))
    );
  }
}
```

Here we are only resolving the newly added fields. So that the functionality of adding the page to the API is all encapsulated in its own module.
