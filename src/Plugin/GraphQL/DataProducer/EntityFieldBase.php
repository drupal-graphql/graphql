<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Base class for entity field data producers.
 */
class EntityFieldBase extends DataProducerPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveItem($item, $property, ResolveContext $context, ResolveInfo $info) {
    if ($item instanceof FieldItemInterface) {
      $result = $item->get($property)->getValue();
      $result = $result instanceof MarkupInterface ? $result->__toString() : $result;

      $type = $info->returnType;
      $type = $type instanceof WrappingType ? $type->getWrappedType(TRUE) : $type;
      if ($type instanceof ScalarType) {
        $result = is_null($result) ? NULL : $type->serialize($result);
      }

      // @todo: handle translations.
      /*if ($result instanceof ContentEntityInterface && $result->isTranslatable() && $language = $context->getContext('language', $info)) {
        if ($result->hasTranslation($language)) {
          $result = $result->getTranslation($language);
        }
      }*/

      return $result;
    }
  }
}