<?php

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;

interface SchemaPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Retrieves the schema.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   *
   * @return \GraphQL\Type\Schema
   *   The schema.
   */
  public function getSchema(ResolverRegistryInterface $registry);

  /**
   * Retrieves the resolver registry.
   *
   * TODO: Instead, this should be configuration.
   *
   * @return \Drupal\graphql\GraphQL\ResolverRegistryInterface
   *   The resolver registry.
   */
  public function getResolverRegistry();

}
