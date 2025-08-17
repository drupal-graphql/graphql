<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\TypedData;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\typed_data\DataFetcherTrait;
use Drupal\typed_data\Exception\InvalidArgumentException;

/**
 * Resolves a typed data value at a given property path.
 */
#[DataProducer(
  id: "property_path",
  name: new TranslatableMarkup("Property path"),
  description: new TranslatableMarkup("Resolves a typed data value at a given property path."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Property value")
  ),
  consumes: [
    "path" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Property path")
    ),
    "value" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Root value")
    ),
    "type" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Root type"),
      required: FALSE
    ),
  ]
)]
class PropertyPath extends DataProducerPluginBase {
  use TypedDataTrait;
  use DataFetcherTrait;

  /**
   * Resolve the property path.
   *
   * @param string $path
   *   The property path to resolve.
   * @param mixed $value
   *   The root value to resolve the path from.
   * @param string|null $type
   *   The type of the value to start from, it will be used to get the
   *   corresponding type data definition and wrap the value in it.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The refinable metadata object.
   *
   * @return mixed
   *   The resolved value at the given property path, or NULL if not found.
   */
  public function resolve(string $path, mixed $value, ?string $type, RefinableCacheableDependencyInterface $metadata): mixed {
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
