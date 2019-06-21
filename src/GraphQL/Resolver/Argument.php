<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

class Argument implements ResolverInterface {

  /**
   * Name of the argument.
   *
   * @var string
   */
  protected $name;

  /**
   * Constructor.
   *
   * @param string $name
   *   Name of the argument.
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info) {
    return $args[$this->name] ?? NULL;
  }

}
