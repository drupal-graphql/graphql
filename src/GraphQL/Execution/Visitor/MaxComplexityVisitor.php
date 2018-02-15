<?php

namespace Drupal\graphql\GraphQL\Execution\Visitor;

use Youshido\GraphQL\Exception\ResolveException;
use Youshido\GraphQL\Field\FieldInterface;

class MaxComplexityVisitor implements VisitorInterface {

  /**
   * @var int
   */
  protected $defaultCost;

  /**
   * MaxComplexityQueryVisitor constructor.
   *
   * @param int $maxComplexity
   *   The allowed maximum complexity.
   */
  public function __construct($defaultCost = 1) {
    $this->defaultCost = $defaultCost;
  }

  /**
   * {@inheritdoc}
   */
  public function visit(array $args, FieldInterface $field, $children) {
    /** @var \Youshido\GraphQL\Config\Field\FieldConfig $config */
    $config = $field->getConfig();
    $cost = $config->get('cost', NULL);

    if (is_callable($cost)) {
      $cost = $cost($args, $field, $children);
    }

    return isset($cost) ? $cost : $this->defaultCost;
  }

  /**
   * {@inheritdoc}
   */
  public function reduce($carry, $current) {
    return !empty($current) ? $carry + $current : $carry;
  }

  /**
   * {@inheritdoc}
   */
  public function initial() {
    return 0;
  }
}