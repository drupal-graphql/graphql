<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\Fields;

use Drupal\graphql_content_mutation\Plugin\GraphQL\DeleteEntityOutputWrapper;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a list of error messages.
 *
 * @GraphQLField(
 *   id = "delete_entity_output_errors",
 *   secure = true,
 *   name = "errors",
 *   type = "String",
 *   types = {"DeleteEntityOutput"},
 *   multi = true,
 *   nullable = false
 * )
 */
class DeleteEntityOutputErrors extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof DeleteEntityOutputWrapper) {
      if ($errors = $value->getErrors()) {
        foreach ($errors as $error) {
          yield $error;
        }
      }
    }
  }

}
