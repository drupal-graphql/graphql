<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL;

use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\GraphQL\Resolver\Argument;
use Drupal\graphql\GraphQL\Resolver\Callback;
use Drupal\graphql\GraphQL\Resolver\Composite;
use Drupal\graphql\GraphQL\Resolver\Condition;
use Drupal\graphql\GraphQL\Resolver\Context;
use Drupal\graphql\GraphQL\Resolver\DefaultValue;
use Drupal\graphql\GraphQL\Resolver\Map;
use Drupal\graphql\GraphQL\Resolver\ParentValue;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\graphql\GraphQL\Resolver\SourceContext;
use Drupal\graphql\GraphQL\Resolver\Tap;
use Drupal\graphql\GraphQL\Resolver\Value;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy;
use Drupal\typed_data\DataFetcherTrait;

/**
 * Wires and maps different resolvers together to build the GraphQL tree.
 */
class ResolverBuilder {
  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * Instantiate a data producer proxy to lazy resolve a data producer plugin.
   */
  public function produce(string $id, array $config = []): DataProducerProxy {
    return DataProducerProxy::create($id, $config);
  }

  /**
   * Combine multiple resolvers in a chain resolving after each other.
   */
  public function compose(ResolverInterface ...$resolvers): Composite {
    return new Composite($resolvers);
  }

  /**
   * Register a resolver.
   */
  public function tap(ResolverInterface $callback): Tap {
    return new Tap($callback);
  }

  /**
   * Register a resolver for multiple items.
   */
  public function map(ResolverInterface $callback): Map {
    return new Map($callback);
  }

  /**
   * Register a callback as resolver.
   *
   * @deprecated in graphql:5.0.0 and is removed from graphql:6.0.0. Create a
   *   class implementing Drupal\graphql\Plugin\DataProducerPluginInterface and
   *   use ResolverBuilder::produce() instead.
   *
   * @see https://www.drupal.org/node/3576383
   */
  public function callback(callable $callback): Callback {
    return new Callback($callback);
  }

  /**
   * Add a context that is available for further resolvers.
   */
  public function context(string $name, ?ResolverInterface $source = NULL): Tap {
    $callback = new SourceContext($name, $source);
    return $this->tap($callback);
  }

  /**
   * Add condition branches to resolve.
   */
  public function cond(array $branches): Condition {
    return new Condition($branches);
  }

  /**
   * Add a property path resolver.
   */
  public function fromPath(string $type, string $path, ?ResolverInterface $value = NULL): DataProducerProxy {
    return $this->produce('property_path')
      ->map('type', $this->fromValue($type))
      ->map('path', $this->fromValue($path))
      ->map('value', $value ?: $this->fromParent());
  }

  /**
   * Adds a fixed value to resolve to.
   */
  public function fromValue(mixed $value): Value {
    return new Value($value);
  }

  /**
   * Adds a query argument value to resolve to.
   */
  public function fromArgument(string $name): Argument {
    return new Argument($name);
  }

  /**
   * Resolves the current value that will be a parent for the field.
   */
  public function fromParent(): ParentValue {
    return new ParentValue();
  }

  /**
   * Resolves a value from the context by context name.
   */
  public function fromContext(string $name, ?callable $default = NULL): Context {
    return new Context($name, $default);
  }

  /**
   * Adds a default value resolver.
   */
  public function defaultValue(mixed $value, mixed $default): DefaultValue {
    return new DefaultValue($value, $default);
  }

}
