<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves by setting the value as context with the given name.
 */
class SourceContext implements ResolverInterface {

  /**
   * Name of the context.
   */
  protected string $name;

  /**
   * Source resolver.
   */
  protected mixed $source;

  /**
   * SourceContext constructor.
   */
  public function __construct(string $name, ?ResolverInterface $source = NULL) {
    $this->name = $name;
    $this->source = $source;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $source = $this->source ?? new ParentValue();
    $context = $source->resolve($value, $args, $context, $info, $field);
    $field->setContextValue($this->name, $context);
    return $context;
  }

}
