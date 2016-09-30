<?php

namespace Drupal\graphql\GraphQL\Relay\Field;

use Drupal\graphql\TypeResolver\TypeResolverWithRelaySupportInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Relay\Field\GlobalIdField as GlobalIdFieldBase;

class GlobalIdField extends GlobalIdFieldBase implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    /** @var \Drupal\graphql\TypeResolver\TypeResolverInterface $typeResolver */
    $typeResolver = $this->container->get('graphql.type_resolver');
    if ($typeResolver instanceof TypeResolverWithRelaySupportInterface) {
      if ($typeResolver->canResolveRelayGlobalId($this->typeName, $value)) {
        return $typeResolver->resolveRelayGlobalId($this->typeName, $value);
      }
    }

    return parent::resolve($value, $args, $info);
  }
}