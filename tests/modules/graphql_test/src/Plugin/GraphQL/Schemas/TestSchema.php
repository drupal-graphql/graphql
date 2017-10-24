<?php

namespace Drupal\graphql_test\Plugin\GraphQL\Schemas;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Schemas\SchemaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\InternalSchemaMutationObject;
use Youshido\GraphQL\Schema\InternalSchemaQueryObject;

/**
 * Default generated schema.
 *
 * @GraphQLSchema(
 *   id = "test",
 *   name = "Default",
 *   path = "/graphql",
 *   builder = "\Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder"
 * )
 */
class TestSchema extends SchemaPluginBase implements SchemaPluginInterface {

  /**
   * {@inheritdoc}
   */
  protected function constructCacheMetadata(SchemaBuilderInterface $schemaBuilder) {
    parent::constructCacheMetadata($schemaBuilder);

    $metadata = new CacheableMetadata();
    $metadata->addCacheContexts(['user']);

    $this->responseMetadata->addCacheableDependency($metadata);
  }
}
