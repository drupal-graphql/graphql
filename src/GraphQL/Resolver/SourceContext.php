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
   * SourceContext constructor.
   *
   * @param string $name
   *   Name of the context.
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface|null $source
   *   Source resolver.
   */
  public function __construct(
    protected string $name,
    protected ?ResolverInterface $source = NULL,
  ) {
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
