<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\TypeInterface;

trait SchemaProphecyTrait {

  /**
   * A schema provider prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $schemaManager;

  /**
   * Factory method that will return the prophesized schema provider service.
   */
  public function schemaManagerProphecyFactory() {
    return $this->schemaManager->reveal();
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $this->injectSchemaManager($container);
    parent::register($container);
  }

  /**
   * Create an empty default schema.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param \Youshido\GraphQL\Field\AbstractField $field
   *
   * @return \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface The empty schema plugin.
   *   The empty schema plugin.
   */
  protected function createSchema(ContainerInterface $container, AbstractField $field = NULL) {
    return TestSchema::create($container, $field);
  }

  /**
   * Set the mock schema that will be injected into tests.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface $schema
   *   The schema to inject.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The prophesized schema manager service.
   *
   * @throws \Exception
   */
  protected function injectSchema(SchemaPluginInterface $schema) {
    if (!isset($this->schemaManager)) {
      throw new \Exception('No schema provider prophecy available. Please invoke `injectSchemaManagerProphecy()` in `KernelTestBase::register()`');
    }

    return $this->schemaManager->createInstance('default')->willReturn($schema);
  }

  /**
   * Inject a schema manager prophecy to mock GraphQL fields.
   *
   * To be called in `KernelTestBase::register()`.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The container builder.
   */
  protected function injectSchemaManager(ContainerBuilder $container) {
    $defaultSchemaDefinition = TestSchema::pluginDefinition();

    $this->schemaManager = $this->prophesize(SchemaPluginManager::class);
    $this->schemaManager->getDefinitions()->willReturn([
      'default' => $defaultSchemaDefinition,
    ]);

    $this->schemaManager->getDefinition('default')->willReturn($defaultSchemaDefinition);

    $container->register('plugin.manager.graphql.schema')
      ->setFactory([$this, 'schemaManagerProphecyFactory'])
      ->setClass(get_class($this->prophesize(SchemaPluginManager::class)->reveal()));
  }

  /**
   * Prophesize a field.
   *
   * @param $name
   *   The field name in the schema.
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The fields return type.
   * @param \Drupal\Core\Cache\CacheableMetadata $schemaMetadata
   *   The cacheable schema metadata.
   * @param \Drupal\Core\Cache\CacheableMetadata $responseMetadata
   *   The cacheable response metadata.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The field prophecy.
   */
  protected function prophesizeField($name, TypeInterface $type, CacheableMetadata $schemaMetadata = NULL, CacheableMetadata $responseMetadata = NULL) {
    if (!$schemaMetadata) {
      $schemaMetadata = new CacheableMetadata();
    }

    if (!$responseMetadata) {
      $responseMetadata = new CacheableMetadata();
    }

    $root = $this->prophesize(AbstractField::class)
      ->willImplement(TypeSystemPluginInterface::class);

    $root->getName()->willReturn($name);
    $root->getType()->willReturn($type);
    $root->getConfig()->willReturn(new FieldConfig([
      'name' => $name,
      'type' => $type,
    ]));

    $root->getSchemaCacheMetadata()->willReturn($schemaMetadata);
    $root->getResponseCacheMetadata()->willReturn($responseMetadata);
    $root->getArguments()->willReturn([]);

    return $root;
  }
}