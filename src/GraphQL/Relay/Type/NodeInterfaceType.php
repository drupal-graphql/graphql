<?php

namespace Drupal\graphql\GraphQL\Relay\Type;

use Drupal\graphql\TypeResolver\TypeResolverWithRelaySupportInterface;
use Youshido\GraphQL\Relay\Field\GlobalIdField;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

class NodeInterfaceType extends AbstractInterfaceType {
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'NodeInterface';
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    $config->addField(new GlobalIdField('NodeInterface'));
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    /** @var \Drupal\graphql\TypeResolver\TypeResolverInterface $typeResolver */
    $typeResolver = \Drupal::service('graphql.type_resolver');
    if ($typeResolver instanceof TypeResolverWithRelaySupportInterface) {
      if ($typeResolver->canResolveRelayType($object)) {
        return $typeResolver->resolveRelayType($object);
      }
    }

    return NULL;
  }
}
