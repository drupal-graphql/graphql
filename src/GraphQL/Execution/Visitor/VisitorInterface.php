<?php

namespace Drupal\graphql\GraphQL\Execution\Visitor;

use Youshido\GraphQL\Field\FieldInterface;

interface VisitorInterface {

  /**
   * @return mixed
   */
  public function initial();

  /**
   * @param array $args
   * @param \Youshido\GraphQL\Field\FieldInterface $field
   * @param $child
   *
   * @return mixed
   */
  public function visit(array $args, FieldInterface $field, $child);

  /**
   * @param $carry
   * @param $current
   *
   * @return mixed
   */
  public function reduce($carry, $current);

}