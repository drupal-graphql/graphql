<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\TypedData;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\typed_data\DataFetcherTrait;
use Drupal\typed_data\Exception\InvalidArgumentException;

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
   */
  public function resolve($path, $type, $value, RefinableCacheableDependencyInterface $metadata) {
    $bubbleable = new BubbleableMetadata();
    $data = $this->getTypedDataManager()->create(DataDefinition::create($type), $value);
    $fetcher = $this->getDataFetcher();

    try {
      $output = $fetcher->fetchDataByPropertyPath($data, $path, $bubbleable)->getValue();
    }
    catch (MissingDataException $exception) {
      // There is no data at the given path.
    }
    catch (InvalidArgumentException $exception) {
      // The path is invalid for the source object.
    }
    finally {
      $metadata->addCacheableDependency($bubbleable);
    }

    return $output ?? NULL;
  }

}
