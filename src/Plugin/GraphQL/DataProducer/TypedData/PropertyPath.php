<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\TypedData;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\TypedData\TypedDataInterface;
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
 *     "value" = @ContextDefinition("any",
 *       label = @Translation("Root value")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Root type"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class PropertyPath extends DataProducerPluginBase {
  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * @param string $path
   * @param mixed $value
   * @param string|null $type
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   */
  public function resolve($path, $value, $type = NULL, RefinableCacheableDependencyInterface $metadata) {
    if (!($value instanceof TypedDataInterface) && !empty($type)) {
      $manager = $this->getTypedDataManager();
      $definition = $manager->createDataDefinition($type);
      $value = $manager->create($definition, $value);
    }

    if (!($value instanceof TypedDataInterface)) {
      throw new \BadMethodCallException('Could not derive typed data type.');
    }

    $bubbleable = new BubbleableMetadata();
    $fetcher = $this->getDataFetcher();

    try {
      $output = $fetcher->fetchDataByPropertyPath($value, $path, $bubbleable)->getValue();
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
