<?php

namespace Drupal\graphql_core;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\graphql\TypeResolver\TypeResolverInterface;

/**
 * Plugin based override for the graphql type resolver.
 */
class PluggableTypeResolver implements TypeResolverInterface {

  /**
   * A type manager instance.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $pluginManager;

  /**
   * TypeResolver constructor.
   *
   * @param \Drupal\graphql_core\GraphQLSchemaManagerInterface $pluginManager
   *   An instance of a graphql plugin manager.
   */
  public function __construct(GraphQLSchemaManagerInterface $pluginManager) {
    $this->pluginManager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive(DataDefinitionInterface $definition) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(DataDefinitionInterface $definition) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function collectTypes() {
    return array_values($this->pluginManager->find(function () {
      return TRUE;
    }, [GRAPHQL_CORE_TYPE_PLUGIN]));
  }

}
