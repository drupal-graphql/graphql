<?php

namespace Drupal\graphql_test_custom_schema\Fields;

use Drupal\graphql\GraphQL\CacheableValue;
use Drupal\user\UserInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\StringType;

/**
 * Provides an entity label field.
 */
class UsernameField extends AbstractField {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof UserInterface) {
      return new CacheableValue($value->getDisplayName(), [$value]);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new NonNullType(new StringType());
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'username';
  }

}
