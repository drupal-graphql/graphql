<?php

namespace Drupal\graphql_test_custom_schema\Types;

use Drupal\graphql_test_custom_schema\Fields\NodeIdField;
use Drupal\node\NodeInterface;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

class NodeInterfaceType extends AbstractInterfaceType {

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof NodeInterface && $object->bundle() === 'article') {
      return new ArticleType();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new NodeIdField());
  }
}