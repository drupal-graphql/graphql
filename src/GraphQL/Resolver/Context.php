<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

class Context implements ResolverInterface {

  /**
   * Name of the context.
   *
   * @var mixed
   */
  protected $name;

  /**
   * An arbitrary default value in case the context is not set.
   *
   * @var mixed
   */
  protected $default;

  /**
   * Constructor.
   *
   * @param string $name
   *   Name of the context.
   * @param mixed $default
   *   An arbitrary default value in case the context is not set.
   */
  public function __construct($name, $default = NULL) {
    $this->name = $name;
    $this->default = $default;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field) {
    $output = $field->getContextValue($this->name, $this->default);
    if ($output instanceof CacheableDependencyInterface) {
      $context->addCacheableDependency($output);
    }

    return $output;
  }

}
