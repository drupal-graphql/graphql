<?php

namespace Drupal\graphql;

use Drupal\graphql\Rule\TypeValidationRule;
use MyProject\Proxies\__CG__\stdClass;
use Youshido\GraphQL\Relay\Fetcher\CallableFetcher;
use Youshido\GraphQL\Relay\Field\NodeField;
use Youshido\GraphQL\Schema\Schema;
use Youshido\GraphQL\Type\NonNullType;
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
    $fields = $this->schemaProvider->getQuerySchema();

    $config['query'] = new ObjectType([
      'name' => 'QueryRoot',
      'fields' => $fields,
    ]);

    $config['query']->addField('root', [
      'type' => new NonNullType($config['query']),
      'resolve' => ['@graphql.schema_factory', 'resolveRoot'],
    ]);

    // @todo This needs to be made cacheable.
    $fetcher = new CallableFetcher([$this, 'resolveNode'], [$this, 'resolveType']);
    $config['query']->addField(new NodeField($fetcher));

    if ($mutation = $this->schemaProvider->getMutationSchema()) {
      $config['mutation'] = new ObjectType([
        'name' => 'MutationRoot',
        'fields' => $mutation,
      ]);
    }

    return new Schema($config);
  }

  /**
   * Dummy resolve function.
   *
   * Used to enable adding a recursive reference to the query root for use in
   * a React & Relay setting.
   *
   * https://github.com/facebook/relay/issues/112#issuecomment-170648934
   */
  public function resolveRoot() {
    return [];
  }

  /**
   * Resolves a node given a type and an id.
   */
  public function resolveNode() {
    // @todo Add this.
  }

  /**
   * Resolves a type given an object.
   */
  public function resolveType() {
    // @todo Add this.
  }
}
