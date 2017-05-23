<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;

/**
 * Expose views as root fields.
 *
 * @GraphQLType(
 *   id = "view_result_type",
 *   deriver = "Drupal\graphql_views\Plugin\Deriver\ViewResultTypeDeriver"
 * )
 */
class ViewResultType extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  protected function buildFields(GraphQLSchemaManagerInterface $schemaManager) {
    // Attach the view_count field to all ViewResultType derivatives.
    return array_merge(parent::buildFields($schemaManager), $schemaManager->find(function ($definition) {
      return $definition['id'] === 'view_count';
    }, [GRAPHQL_CORE_FIELD_PLUGIN]));
  }

}
