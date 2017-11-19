<?php

namespace Drupal\graphql_test\Plugin\GraphQL\Schemas;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
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
 *   path = "/graphql"
 * )
 */
class TestSchema extends SchemaPluginBase implements SchemaPluginInterface {

}
