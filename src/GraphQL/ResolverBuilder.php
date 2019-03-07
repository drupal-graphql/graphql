<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
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
   * @param callable $callback
   *
   * @return \Closure
   */
  public function tap(DataProducerInterface $callback) {
    return new DataProducerCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($callback) {
      $callback->resolve($value, $args, $context, $info);
      return $value;
    });
  }

  /**
   * @param $name
   * @param callable|null $source
   *
   * @return \Closure
   */
  public function context($name, DataProducerInterface $source = NULL) {
    return $this->tap(new DataProducerCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($name, $source) {
      $source = $source ?? $this->fromParent();
      $value = $source->resolve($value, $args, $context, $info);
      $context->setContext($name, $value, $info);
    }));
  }

  /**
   * @param array $branches
   *
   * @return \Closure
   */
  public function cond(array $branches) {
    return new DataProducerCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($branches) {
      while (list($condition, $resolver) = array_pad(array_shift($branches), 2, NULL)) {
        if ($condition instanceof DataProducerInterface) {
          if (($condition = $condition->resolve($value, $args, $context, $info)) === NULL) {
            // Bail out early if a resolver returns NULL.
            continue;
          }
        }

        if ($condition instanceof Deferred) {
          return DeferredUtility::returnFinally($condition, function ($cond) use ($branches, $resolver, $value, $args, $context, $info) {
            array_unshift($branches, [$cond, $resolver]);
            return $this->cond($branches)($value, $args, $context, $info);
          });
        }

        if ((bool) $condition) {
          return $resolver ? $resolver->resolve($value, $args, $context, $info) : $condition;
        }
      }

      // Functional languages throw exceptions here. Should we just return NULL?
      return NULL;
    });
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
   * @return \Closure
   */
  public function fromPath($type, $path, DataProducerInterface $value = NULL) {
    return new DataProducerCallable(function ($parent, $args, ResolveContext $context, ResolveInfo $info) use ($type, $path, $value) {
      $value = $value ?? $this->fromParent();
      $value = $value->resolve($parent, $args, $context, $info);
      $metadata = new BubbleableMetadata();

      $type = $type instanceof DataDefinitionInterface ? $type : DataDefinition::create($type);
      $data = $this->getTypedDataManager()->create($type, $value);
      $output = $this->getDataFetcher()->fetchDataByPropertyPath($data, $path, $metadata)->getValue();

      $context->addCacheableDependency($metadata);
      if ($output instanceof CacheableDependencyInterface) {
        $context->addCacheableDependency($output);
      }

      return $output;
    });
  }

  /**
   * @param $value
   *
   * @return \Closure
   */
  public function fromValue($value) {
    return new DataProducerCallable(function ($parent, $args, ResolveContext $context, ResolveInfo $info) use ($value) {
      if ($value instanceof CacheableDependencyInterface) {
        $context->addCacheableDependency($value);
      }

      return $value;
    });
  }

  /**
   * @param $name
   *
   * @return \Closure
   */
  public function fromArgument($name) {
    return new DataProducerCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($name) {
      return $args[$name] ?? NULL;
    });
  }

  /**
   * @return \Closure
   */
  public function fromParent() {
    return new DataProducerCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
      if ($value instanceof CacheableDependencyInterface) {
        $context->addCacheableDependency($value);
      }

      return $value;
    });
  }

  /**
   * @param $name
   * @param callable|null $default
   *
   * @return \Closure
   */
  public function fromContext($name, $default = NULL) {
    return new DataProducerCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($name, $default) {
      $output = $context->getContext($name, $info, $default);
      if ($output instanceof CacheableDependencyInterface) {
        $context->addCacheableDependency($output);
      }

      return $output;
    });
  }

}
