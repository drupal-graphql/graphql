<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Conditional resolver.
 *
 * From given set of conditions and their respective resolvers it resolves the
 * one whose condition is evaluated with non empty value.
 */
class Condition implements ResolverInterface {

  /**
   * List of condition and their corresponding resolvers.
   *
   * @var array
   */
  protected $branches;

  /**
   * Constructor.
   *
   * @param array $branches
   *   List of condition and their corresponding resolvers.
   */
  public function __construct(array $branches) {
    $this->branches = $branches;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info) {
    $branches = $this->branches;
    while (list($condition, $resolver) = array_pad(array_shift($branches), 2, NULL)) {
      if ($condition instanceof ResolverInterface) {
        if (($condition = $condition->resolve($value, $args, $context, $info)) === NULL) {
          // Bail out early if a resolver returns NULL.
          continue;
        }
      }

      if ($condition instanceof Deferred) {
        return DeferredUtility::returnFinally($condition, function ($cond) use ($branches, $resolver, $value, $args, $context, $info) {
          array_unshift($branches, [$cond, $resolver]);
          return (new Condition($branches))->resolve($value, $args, $context, $info);
        });
      }

      if ((bool) $condition) {
        /** @var \Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver */
        return $resolver ? $resolver->resolve($value, $args, $context, $info) : $condition;
      }
    }

    // Functional languages throw exceptions here. Should we just return NULL?
    return NULL;
  }

}
