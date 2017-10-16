<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Field\FieldItemBase;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Base class for entity field plugins.
 */
class EntityFieldBase extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveItem($item) {
    if ($item instanceof FieldItemBase) {
      $definition = $this->getPluginDefinition();
      $property = $definition['property'];
      $type = $definition['type'];
      $result = $item->get($property)->getValue();

      if ($type === 'Int') {
        $result = (int) $result;
      }
      elseif ($type === 'Float') {
        $result = (float) $result;
      }

      return $result;
    }
  }

}
