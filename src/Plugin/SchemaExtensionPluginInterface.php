<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use GraphQL\Language\Source;

/**
 * Defines plugins that can extend the GraphQL schema definition.
 */
interface SchemaExtensionPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Registers type and field resolvers in the shared registry.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The resolver registry.
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void;

  /**
   * Retrieves the base schema definition.
   *
   * @return \GraphQL\Language\Source|null
   *   The base schema definition.
   */
  public function getBaseDefinition(): ?Source;

  /**
   * Retrieves the extension schema definition.
   *
   * @return \GraphQL\Language\Source|null
   *   The extension schema definition.
   */
  public function getExtensionDefinition(): ?Source;

}
