<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\SubrequestField;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Request arbitrary drupal context objects with GraphQL.
 *
 * @GraphQLField(
 *   id = "context",
 *   types = {"Url"},
 *   nullable = true,
 *   deriver = "\Drupal\graphql_core\Plugin\Deriver\ContextDeriver"
 * )
 */
class Context extends SubrequestField {

  /**
   * {@inheritdoc}
   */
  protected function buildType(GraphQLSchemaManagerInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (array_key_exists('data_type', $definition) && $definition['data_type']) {
        $types = $schemaManager->find(function ($def) use ($definition) {
          return array_key_exists('data_type', $def) && $def['data_type'] == $definition['data_type'];
        }, [
          GRAPHQL_CORE_TYPE_PLUGIN,
          GRAPHQL_CORE_INTERFACE_PLUGIN,
          GRAPHQL_CORE_SCALAR_PLUGIN,
        ]);

        return array_pop($types) ?: $schemaManager->findByName('String', [GRAPHQL_CORE_SCALAR_PLUGIN]);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $definition = $this->getPluginDefinition();
    return parent::resolve(NULL, [
      'extract' => $definition['context_id'],
      'path' => $value instanceof Url ? $value->toString() : NULL,
    ], $info);
  }

}
