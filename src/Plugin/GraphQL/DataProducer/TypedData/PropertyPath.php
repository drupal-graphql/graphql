<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\TypedData;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\typed_data\DataFetcherTrait;

/**
 * @DataProducer(
 *   id = "property_path",
 *   name = @Translation("Property path"),
 *   description = @Translation("Resolves a typed data value at a given property path."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Property value")
 *   ),
 *   consumes = {
 *     "path" = @ContextDefinition("string",
 *       label = @Translation("Property path")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Root type")
 *     ),
 *     "value" = @ContextDefinition("any",
 *       label = @Translation("Root value")
 *     )
 *   }
 * )
 */
class PropertyPath extends DataProducerPluginBase {
  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * @param string $path
   * @param string $type
   * @param mixed $value
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\typed_data\Exception\InvalidArgumentException
   */
  public function resolve($path, $type, $value, RefinableCacheableDependencyInterface $metadata) {
    $bubbleable = new BubbleableMetadata();
    $data = $this->getTypedDataManager()->create(DataDefinition::create($type), $value);
    $output = $this->getDataFetcher()->fetchDataByPropertyPath($data, $path, $bubbleable)->getValue();
    $metadata->addCacheableDependency($bubbleable);

    return $output;
  }

}
