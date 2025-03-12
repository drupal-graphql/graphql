<?php

declare(strict_types=1);

namespace Drupal\graphql_schema_extension_test\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * A test extension.
 *
 * @SchemaExtension(
 *   id = "test",
 *   name = "Test",
 *   schema = "composable"
 * )
 */
class TestExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDependencies(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionDependencies(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('TestType', 'pluginId', $builder->callback(
      fn (): string => $this->getPluginDefinition()['id']
    ));
  }

}
