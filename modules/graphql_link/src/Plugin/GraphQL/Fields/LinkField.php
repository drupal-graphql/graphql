<?php

namespace Drupal\graphql_link\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose entity reference fields as objects.
 *
 * @GraphQLField(
 *   id = "link_item",
 *   field_formatter = "link",
 *   type = "LinkItem",
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver"
 * )
 */
class LinkField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      foreach ($value->get($this->getPluginDefinition()['field']) as $link) {
        yield $link;
      }
    }
  }

}
