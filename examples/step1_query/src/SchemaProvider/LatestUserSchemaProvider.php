<?php

namespace Drupal\graphql_example_query\SchemaProvider;

use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\SchemaProvider\SchemaProviderBase;
use Drupal\graphql\TypeResolver\TypeResolverInterface;
use Drupal\graphql_example_query\GraphQL\Field\Root\LatestUserField;

/**
 * Class LatestUserSchemaProvider exposes a "latest user" field.
 */
class LatestUserSchemaProvider extends SchemaProviderBase {

  /**
   * The type resolver service.
   *
   * @var \Drupal\graphql\TypeResolver\TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * The typed data manager service.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * LatestUserSchemaProvider constructor.
   *
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed_data_manager service.
   * @param \Drupal\graphql\TypeResolver\TypeResolverInterface $typeResolver
   *   The graphql.type_resolver service.
   */
  public function __construct(TypedDataManagerInterface $typedDataManager, TypeResolverInterface $typeResolver) {
    $this->typedDataManager = $typedDataManager;
    $this->typeResolver = $typeResolver;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    $typeId = 'entity:user';
    $dataDefinition = $this->typedDataManager->createDataDefinition($typeId);
    $outputType = $this->typeResolver->resolveRecursive($dataDefinition);
    $fields = [
      new LatestUserField($outputType),
    ];
    return $fields;
  }

}
