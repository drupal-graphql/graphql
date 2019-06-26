<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\GraphQL\Resolver\Argument;
use Drupal\graphql\GraphQL\Resolver\Callback;
use Drupal\graphql\GraphQL\Resolver\Composite;
use Drupal\graphql\GraphQL\Resolver\Condition;
use Drupal\graphql\GraphQL\Resolver\Context;
use Drupal\graphql\GraphQL\Resolver\Map;
use Drupal\graphql\GraphQL\Resolver\ParentValue;
use Drupal\graphql\GraphQL\Resolver\Path;
use Drupal\graphql\GraphQL\Resolver\SourceContext;
use Drupal\graphql\GraphQL\Resolver\Tap;
use Drupal\graphql\GraphQL\Resolver\Value;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy;
use Drupal\typed_data\DataFetcherTrait;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;

class ResolverBuilder {
  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * @param $id
   * @param $config
   *
   * @return \Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy
   */
  public function produce($id, $config = []) {
    return DataProducerProxy::create($id, $config);
  }

  /**
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface[] $resolvers
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Composite
   */
  public function compose(ResolverInterface ...$resolvers) {
    return new Composite($resolvers);
  }

  /**
   * @param ResolverInterface $callback
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Tap
   */
  public function tap(ResolverInterface $callback) {
    return new Tap($callback);
  }

  /**
   * @param ResolverInterface $callback
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Map
   */
  public function map(ResolverInterface $callback) {
    return new Map($callback);
  }

  /**
   * @param callable $callback
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Callback
   */
  public function callback(callable $callback) {
    return new Callback($callback);
  }

  /**
   * @param $name
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $source
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Tap
   */
  public function context($name, ResolverInterface $source = NULL) {
    $callback = new SourceContext($name, $source);
    return $this->tap($callback);
  }

  /**
   * @param array $branches
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Condition
   */
  public function cond(array $branches) {
    return new Condition($branches);
  }

  /**
   * @param $type
   * @param $path
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $value
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Path
   */
  public function fromPath($type, $path, ResolverInterface $value = NULL) {
    return new Path($type, $path, $value);
  }

  /**
   * @param $value
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Value
   */
  public function fromValue($value) {
    return new Value($value);
  }

  /**
   * @param $name
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Argument
   */
  public function fromArgument($name) {
    return new Argument($name);
  }

  /**
   * @return \Drupal\graphql\GraphQL\Resolver\ParentValue
   */
  public function fromParent() {
    return new ParentValue();
  }

  /**
   * @param $name
   * @param callable|null $default
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Context
   */
  public function fromContext($name, $default = NULL) {
    return new Context($name, $default);
  }

}
