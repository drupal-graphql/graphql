<?php
/**
 * Created by PhpStorm.
 * User: fubhy
 * Date: 18.09.18
 * Time: 12:53
 */

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Utility\DeferredUtility;
use GraphQL\Type\Definition\ResolveInfo;

trait DataProducerTrait {
  use DataProducerInputTrait;
  use DataProducerCachingTrait;

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return mixed
   * @throws \Exception
   */
  public function __invoke($value, $args, ResolveContext $context, ResolveInfo $info) {
    // Allow arguments to be resolved lazily too.
    $values = DeferredUtility::waitAll($this->getInputValues($value, $args, $context, $info));
    return DeferredUtility::returnFinally($values, function ($values) use ($context, $info) {
      $metadata = new CacheableMetadata();
      $metadata->addCacheContexts(['user.permissions']);

      $output = $this->shouldLookupEdgeCache($values, $context, $info) ?
        $this->resolveCached($values, $context, $info, $metadata) :
        $this->resolveUncached($values, $context, $info, $metadata);

      return DeferredUtility::applyFinally($output, function () use ($context, $metadata) {
        $context->addCacheableDependency($metadata);
      });
    });
  }

}