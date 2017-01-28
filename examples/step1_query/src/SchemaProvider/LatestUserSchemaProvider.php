<?php

namespace Drupal\graphql_example_query\SchemaProvider;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\SchemaProvider\SchemaProviderBase;
use Drupal\graphql\TypeResolver\TypeResolverInterface;
use Drupal\graphql_example_query\GraphQL\Field\Root\LatestUserField;

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
