<?php

namespace Drupal\graphql\GraphQL\Field;

use Drupal\graphql\GraphQL\Batching\BatchedFieldInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

class BatchedField extends Field implements BatchedFieldInterface {

  /**
   * {@inheritdoc}
   */
  public function getBatchedFieldResolver($parent, array $arguments, ResolveInfo $info) {
    if ($this->plugin instanceof BatchedFieldInterface) {
      return $this->plugin->getBatchedFieldResolver($parent, $arguments, $info);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchId($parent, array $arguments, ResolveInfo $info) {
    if ($this->plugin instanceof BatchedFieldInterface) {
      return $this->plugin->getBatchId($parent, $arguments, $info);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBatch(array $batch) {
    if ($this->plugin instanceof BatchedFieldInterface) {
      return $this->plugin->resolveBatch($batch);
    }

    return NULL;
  }
}
