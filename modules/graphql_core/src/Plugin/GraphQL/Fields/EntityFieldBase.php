<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;

/**
 * Base class for entity field plugins.
 */
class EntityFieldBase extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveItem($item) {
    if ($item instanceof FieldItemInterface) {
      $definition = $this->getPluginDefinition();
      $property = $definition['property'];
      $type = $definition['type'];
      // @TODO Add smarter resolving (e.g. buffering for entity references).
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
