<?php

namespace Drupal\graphql\GraphQL\Visitors;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use GraphQL\Language\AST\OperationDefinition;
use GraphQL\Validator\ValidationContext;

class CacheMetadataCalculator {

  /**
   * @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface
   */
  protected $metadata;

  /**
   * CacheMetadataCalculator constructor.
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   */
  public function __construct(RefinableCacheableDependencyInterface $metadata) {
    $this->metadata = $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function calculate(OperationDefinition $definition, ValidationContext $context, \ArrayObject $variables, \ArrayObject $structure) {
    // TODO: Calculate cache metadata.
  }
}