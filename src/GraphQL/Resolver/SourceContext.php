<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves a source and sets context.
 */
class SourceContext implements ResolverInterface {

  /**
   * Name of the context.
   *
   * @var string
   */
  protected $name;

  /**
   * Source resolver.
   *
   * @var mixed
   */
  protected $source;

  /**
   * Constructor.
   *
   * @param string $name
   *   Name of the context.
   * @param mixed $source
   *   Source resolver.
   */
  public function __construct($name, $source = NULL) {
    $this->name = $name;
    $this->source = $source;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info) {
    $source = $this->source ?? new ParentValue();
    $value = $source->resolve($value, $args, $context, $info);
    $context->setContext($this->name, $value, $info);
  }

}
