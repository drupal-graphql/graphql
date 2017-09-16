<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\SchemaProvider\SchemaProviderInterface;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Schema\Schema;
use Youshido\GraphQL\Type\TypeInterface;

trait SchemaProphecyTrait {

  /**
   * A schema provider prophecy.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  private $schemaProvider;

  /**
   * Factory method that will return the prophesized schema provider service.
   */
  public function schemaProviderProphecyFactory() {
    return $this->schemaProvider->reveal();
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $this->injectSchemaProviderProphecy($container);
    parent::register($container);
  }

  /**
   * Set the mock schema that will be injected into tests.
   *
   * @throws \Exception
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The schema provider prophecy.
   */
  protected function injectSchema(Schema $schema) {
    if (!isset($this->schemaProvider)) {
      throw new \Exception('No schema provider prophecy available. Please invoke `injectSchemaProviderProphecy()` in `KernelTestBase::register()`');
    }
    return $this->schemaProvider->getSchema()->willReturn($schema);
  }

  /**
   * Inject a schema provider prophecy to mock GraphQL fields.
   *
   * To be called in `KernelTestBase::register()`.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The container builder.
   */
  protected function injectSchemaProviderProphecy(ContainerBuilder $container) {
    $this->schemaProvider = $this->prophesize(SchemaProviderInterface::class);
    $container->register('graphql_schema_provider')
      ->setFactory([$this, 'schemaProviderProphecyFactory'])
      ->setClass(get_class($this->prophesize(SchemaProviderInterface::class)->reveal()))
      ->addTag('graphql_schema_provider');
  }

  /**
   * Prophesize a field.
   *
   * @param $name
   *   The field name in the schema.
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The fields return type.
   * @param \Drupal\Core\Cache\CacheableMetadata $metadata
   *   The cacheable metadata.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The field prophecy.
   */
  protected function prophesizeField($name, TypeInterface $type, CacheableMetadata $metadata = NULL) {
    if (!$metadata) {
      $metadata = new CacheableMetadata();
    }

    $root = $this->prophesize(AbstractField::class)
      ->willImplement(CacheableDependencyInterface::class);

    $root->getName()->willReturn($name);
    $root->getType()->willReturn($type);
    $root->getConfig()->willReturn(new FieldConfig([
      'name' => $name,
      'type' => $type,
    ]));

    $root->getCacheTags()->willReturn($metadata->getCacheTags());
    $root->getCacheContexts()->willReturn($metadata->getCacheContexts());
    $root->getCacheMaxAge()->willReturn($metadata->getCacheMaxAge());
    $root->getArguments()->willReturn([]);

    return $root;
  }
}