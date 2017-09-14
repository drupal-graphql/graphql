<?php

namespace Drupal\graphql_views\Plugin\GraphQL\Fields;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose view row fields for configured fieldable views.
 *
 * @GraphQLField(
 *   id = "view_row_field",
 *   secure = true,
 *   nullable = true,
 *   deriver = "Drupal\graphql_views\Plugin\Deriver\ViewRowFieldDeriver"
 * )
 */
class ViewRowField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    $definition = $this->getPluginDefinition();
    if (isset($value[$definition['field']])) {
      yield $value[$definition['field']];
    }
  }
}
