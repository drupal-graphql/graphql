<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\graphql_content_mutation\Plugin\GraphQL\CreateEntityOutputWrapper;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a list of error messages.
 *
 * @GraphQLField(
 *   id = "create_entity_output_errors",
 *   name = "errors",
 *   type = "String",
 *   types = {"CreateEntityOutput"},
 *   multi = true,
 *   nullable = false
 * )
 */
class CreateEntityOutputErrors extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof CreateEntityOutputWrapper) {
      if ($errors = $value->getErrors()) {
        foreach ($errors as $error) {
          yield $error;
        }
      }
    }
  }

}
