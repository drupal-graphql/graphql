<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\typed_data\DataFetcherTrait;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerInterface;

/**
 * Resolves a path.
 */
class Path implements DataProducerInterface {

  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * Name of the context.
   *
   * @var string
   */
  protected $type;

  /**
   * Source resolver.
   *
   * @var mixed
   */
  protected $path;

  /**
   * Resolver.
   *
   * @var mixed
   */
  protected $value;

  /**
   * Constructor.
   *
   * @param string $type
   *   Entity type.
   * @param string $path
   *   Path to get value.
   * @param \Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerInterface $value
   *   Resolver.
   */
  public function __construct($type, $path, DataProducerInterface $value = NULL) {
    $this->type = $type;
    $this->path = $path;
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($parent, $args, ResolveContext $context, ResolveInfo $info) {
    $value = $this->value ?? new ParentValue();
    $value = $value->resolve($parent, $args, $context, $info);
    $metadata = new BubbleableMetadata();

    $type = $this->type instanceof DataDefinitionInterface ? $this->type : DataDefinition::create($this->type);
    $data = $this->getTypedDataManager()->create($type, $value);
    $output = $this->getDataFetcher()->fetchDataByPropertyPath($data, $this->path, $metadata)->getValue();

    $context->addCacheableDependency($metadata);
    if ($output instanceof CacheableDependencyInterface) {
      $context->addCacheableDependency($output);
    }

    return $output;
  }

}
