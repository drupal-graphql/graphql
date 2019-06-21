# Splitting up the schema and builder over multiple modules

It is possible to split up the schema into MODULENAME.gql files that live in Drupal module folders.

You need to use `SdlModuleSchemaPluginBase`, for example like this:

```php
<?php
namespace Drupal\example\Plugin\GraphQL\Schema;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlModuleSchemaPluginBase;
use Drupal\graphql\Plugin\ResolverMapPluginManager;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Example GraphQL schema.
 *
 * @Schema(
 *   id = "example",
 *   name = "Example schema"
 * )
 */
class SdlSchemaExample extends SdlModuleSchemaPluginBase {
  /**
   * GraphQL resolver registry map manager.
   *
   * @var \Drupal\graphql\Plugin\ResolverMapPluginManager
   */
  protected $registryMapManager;
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, CacheBackendInterface $astCache, $config, ModuleHandlerInterface $module_handler, ResolverMapPluginManager $registry_map_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $astCache, $config, $module_handler);
    $this->registryMapManager = $registry_map_manager;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.graphql.ast'),
      $container->getParameter('graphql.config'),
      $container->get('module_handler'),
      $container->get('plugin.manager.graphql.resolver_map')
    );
  }
  /**
   * {@inheritdoc}
   */
  protected function getResolverRegistry() {
    $registry = new ResolverRegistry([], [
      __CLASS__,
      'defaultFieldResolver',
    ]);
    return $this->registryMapManager->registerResolvers($this->getPluginId(), $registry);
  }
  /**
   * The default field resolver.
   *
   * Used if no field resolver was explicitly registered.
   *
   * @param mixed $source
   *   The source (parent) value.
   * @param array $args
   *   An array of arguments.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The context object.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   *
   * @return mixed
   *   The result for the field.
   */
  public static function defaultFieldResolver($source, array $args, ResolveContext $context, ResolveInfo $info) {
    $fieldName = $info->fieldName;
    $property = NULL;
    if (is_array($source) || $source instanceof \ArrayAccess) {
      if (isset($source[$fieldName])) {
        $property = $source[$fieldName];
      }
    }
    else {
      if (is_object($source) && isset($source->{$fieldName})) {
        $property = $source->{$fieldName};
      }
      // Allow methods on wrapper objects with the same name to be used as
      // callbacks to resolve the field value.
      else {
        if (is_callable([$source, $fieldName])) {
          $property = [$source, $fieldName];
        }
      }
    }
    if (is_callable($property)) {
      return $property($source, $args, $context, $info);
    }
    return $property;
  }
}
```

Then you can specify your graphql schema in a MODULENAME.gql file:

```gql
schema {
  query: Query
}
type Query {
  article(id: Int!): Article
  page(id: Int!): Page
  node(id: Int!): NodeInterface
  label(id: Int!): String
}
type Article implements NodeInterface {
  id: Int!
  uid: String
  title: String!
  render: String
}
type Page implements NodeInterface {
  id: Int!
  uid: String
  title: String
}
interface NodeInterface {
  id: Int!
}
```

And the resolver mapping in an `@ResolverMap` plugin:

```php
<?php
namespace Drupal\example\Plugin\GraphQL\ResolverMap;
use Drupal\Core\Plugin\PluginBase;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\ResolverMapPluginInterface;
/**
 * Registers common field/type resolvers.
 *
 * @ResolverMap(
 *   id = "example_common_resolvers",
 *   schema = "example",
 * )
 */
class CommonResolvers extends PluginBase implements ResolverMapPluginInterface {
  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'node',
      $builder->produce('entity_load', ['mapping' => [
        'entity_type' => $builder->fromValue('node'),
        'entity_id' => $builder->fromArgument('id'),
      ]])
    );
    $registry->addFieldResolver('Query', 'label',
      $builder->produce('entity_label', ['mapping' => [
        'entity' => $builder->produce('entity_load', ['mapping' => [
          'entity_type' => $builder->fromValue('node'),
          'entity_bundle' => $builder->fromValue(['article']),
          'entity_id' => $builder->fromArgument('id'),
        ]])
      ]])
    );
    // ... Add more field resolvers here.
  }
}
```

A module can also extend the schema of another module with MODULENAME.extend.gql:

```gql
extend type Query {
  resumeByUserId(userId: Int): ContentResponse
}
extend type Mutation {
  updateResume(id: Int, data: ResumeInput): ContentResponse
}
extend union Content = Resume
```

The field resolvers for the schema extension are added with the others in `@ResolverMap` plugins.
