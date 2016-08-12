<?php

namespace Drupal\graphql;

use Drupal\graphql\Rule\TypeValidationRule;
use Youshido\GraphQL\Schema\Schema;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Validator\ConfigValidator\ConfigValidator;

/**
 * Loads and caches a generated GraphQL schema.
 */
class SchemaFactory {
  /**
   * The schema provider service.
   *
   * @var \Drupal\graphql\SchemaProviderInterface
   */
  protected $schemaProvider;

  /**
   * Constructs a SchemaLoader object.
   *
   * @param \Drupal\graphql\SchemaProviderInterface $schemaProvider
   *   The schema provider service.
   */
  public function __construct(SchemaProviderInterface $schemaProvider) {
    // Override the default type validator to enable services as field resolver
    // callbacks.
    $validator = ConfigValidator::getInstance();
    $validator->addRule('type', new TypeValidationRule($validator));

    $this->schemaProvider = $schemaProvider;
  }

  /**
   * Loads and caches the generated schema.
   *
   * @return \Youshido\GraphQL\Schema\Schema The generated GraphQL schema.
   *   The generated GraphQL schema.
   */
  public function getSchema() {
    $config['query'] = new ObjectType([
      'name' => 'QueryRoot',
      'fields' => $this->schemaProvider->getQuerySchema(),
    ]);

    if ($mutation = $this->schemaProvider->getMutationSchema()) {
      $config['mutation'] = new ObjectType([
        'name' => 'MutationRoot',
        'fields' => $mutation,
      ]);
    }

    return new Schema($config);
  }
}
