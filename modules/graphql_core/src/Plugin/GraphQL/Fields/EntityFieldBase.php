<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

/**
 * Base class for entity field plugins.
 */
class EntityFieldBase extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveItem($item, array $args, ResolveInfo $info) {
    if ($item instanceof FieldItemInterface) {
      $definition = $this->getPluginDefinition();
      $property = $definition['property'];
      $result = $item->get($property)->getValue();

      if (($type = $info->getReturnType()->getNamedType()) && $type instanceof AbstractScalarType) {
        $result = $type->serialize($result);
      }

      return $result;
    }
  }

}
