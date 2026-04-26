<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use GraphQL\Type\Schema;

/**
 * Defines a schema plugin that returns a GraphQL schema part.
 *
 * A schema plugin also defines how that schema is resolved to values with data
 * producers.
 */
interface SchemaPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Retrieves the schema.
   *
   * @return \GraphQL\Type\Schema
   *   The schema.
   */
  public function getSchema(): Schema;

  /**
   * Retrieves the resolver registry.
   *
   * @return \Drupal\graphql\GraphQL\ResolverRegistryInterface
   *   The resolver registry.
   */
  public function getResolverRegistry(): ResolverRegistryInterface;

}
