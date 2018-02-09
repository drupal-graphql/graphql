<?php

namespace Drupal\graphql\GraphQL\Execution;

use Youshido\GraphQL\Execution\DeferredResolverInterface;
use Youshido\GraphQL\Execution\DeferredResult;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Type\Enum\AbstractEnumType;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

class Processor extends BaseProcessor {

  /**
   * {@inheritdoc}
   */
  protected function deferredResolve($resolvedValue, FieldInterface $field, callable $callback) {
    if ($resolvedValue instanceof DeferredResolverInterface) {
      $deferredResult = new DeferredResult($resolvedValue, function ($resolvedValue) use ($field, $callback) {
        // Allow nested deferred resolvers.
        return $this->deferredResolve($resolvedValue, $field, $callback);
      });

      // Whenever we stumble upon a deferred resolver, add it to the queue to be
      // resolved later.
      $type = $field->getType()->getNamedType();
      if ($type instanceof AbstractScalarType || $type instanceof AbstractEnumType) {
        array_push($this->deferredResultsLeaf, $deferredResult);
      }
      else {
        array_push($this->deferredResultsComplex, $deferredResult);
      }

      return $deferredResult;
    }

    // For simple values, invoke the callback immediately.
    return $callback($resolvedValue);
  }

}