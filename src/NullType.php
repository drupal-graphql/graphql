<?php

/**
 * @file
 * Contains \Drupal\graphql\NullType.
 */

namespace Drupal\graphql;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;

class NullType extends ScalarType {

  protected $name = 'Null';

  /**
   * @param mixed $value
   *
   * @return mixed
   */
  public function coerce($value)
  {
    return NULL;
  }

  /**
   * @param \Fubhy\GraphQL\Language\Node $value
   *
   * @return mixed
   */
  public function coerceLiteral(Node $value)
  {
    return 'null';
  }
}
