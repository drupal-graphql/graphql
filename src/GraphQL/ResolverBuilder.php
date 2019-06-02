<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\Argument;
use Drupal\graphql\GraphQL\Resolver\Condition;
use Drupal\graphql\GraphQL\Resolver\Context;
use Drupal\graphql\GraphQL\Resolver\ParentValue;
use Drupal\graphql\GraphQL\Resolver\Path;
use Drupal\graphql\GraphQL\Resolver\SourceContext;
use Drupal\graphql\GraphQL\Resolver\Tap;
use Drupal\graphql\GraphQL\Resolver\Value;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use Drupal\typed_data\DataFetcherTrait;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerProxy;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerComposite;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerCallable;

class ResolverBuilder {
  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * @param callable|callable[] ...$resolvers
   *
   * @return \Closure
   */
  public function compose(DataProducerInterface ...$resolvers) {
    return new DataProducerComposite($resolvers);
  }

  /**
   * @param DataProducerInterface $callback
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Tap
   */
  public function tap(DataProducerInterface $callback) {
    return new Tap($callback);
  }

  /**
   * @param $name
   * @param callable|null $source
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Tap
   */
  public function context($name, DataProducerInterface $source = NULL) {
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
   * @param $id
   * @param $config
   *
   * @return DataProducerProxy
   */
  public function produce($id, $config = []) {
    // TODO: Properly inject this.
    $manager = \Drupal::service('plugin.manager.graphql.data_producer');
    return new DataProducerProxy($id, $config, $manager);
  }

  /**
   * @param $type
   * @param $path
   * @param callable|NULL $value
   *
   * @return \Drupal\graphql\GraphQL\Resolver\Path
   */
  public function fromPath($type, $path, DataProducerInterface $value = NULL) {
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
