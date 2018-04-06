<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Link;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Link;

/**
 * Retrieve a link text.
 *
 * @GraphQLField(
 *   id = "link_label",
 *   secure = true,
 *   name = "text",
 *   description = @Translation("The label of a link."),
 *   type = "String",
 *   parents = {"Link"}
 * )
 */
class LinkLabel extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Link) {
      yield $value->getText();
    }
  }

}
