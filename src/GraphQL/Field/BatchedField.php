<?php

namespace Drupal\graphql\GraphQL\Field;

use Drupal\graphql\GraphQL\Batching\BatchedFieldInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

class BatchedField extends Field implements BatchedFieldInterface {

  /**
   * {@inheritdoc}
   */
  public function getBatchedFieldResolver($parent, array $arguments, ResolveInfo $info) {
    if (($plugin = $this->getPluginFromResolveInfo($info)) && $plugin instanceof BatchedFieldInterface) {
      return $plugin->getBatchedFieldResolver($parent, $arguments, $info);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchId($parent, array $arguments, ResolveInfo $info) {
    if (($plugin = $this->getPluginFromResolveInfo($info)) && $plugin instanceof BatchedFieldInterface) {
      return $plugin->getBatchId($parent, $arguments, $info);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBatch(array $batch) {
    if (($plugin = $this->getPluginFromResolveInfo($batch['info'])) && $plugin instanceof BatchedFieldInterface) {
      return $plugin->resolveBatch($batch);
    }

    return NULL;
  }
}
