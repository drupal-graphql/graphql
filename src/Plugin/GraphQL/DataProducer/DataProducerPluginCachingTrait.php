<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityInterface;

/**
 * Cache prefix implementation for data producers.
 */
trait DataProducerPluginCachingTrait {

  /**
   * {@inheritdoc}
   */
  public function edgeCachePrefix(): string {
    $contexts = array_map(static function ($context) {
      if ($context instanceof EntityInterface) {
        return $context->uuid();
      }

      return $context;
    }, $this->getContextValues());

    return hash('xxh64', serialize($contexts));
  }

  /**
   * {@inheritdoc}
   */
  abstract public function getContextValues();

}
