<?php

namespace Drupal\graphql\GraphQL\Relay;

use Drupal\graphql\GraphQL\Relay\Field\NodeField;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Object\ObjectType;

class Schema extends AbstractSchema {
  /**
   * Constructs a Schema object.
   *
   * @param array $query
   *   An array of query fields.
   * @param array $mutation
   *   An array of mutation fields.
   */
  public function __construct(array $query, array $mutation = NULL) {
    $config['query'] = new ObjectType([
      'name' => 'QueryRoot',
      'fields' => [
        'node' => new NodeField(),
      ] + $query,
    ]);

    if (!empty($mutation)) {
      $config['mutation'] = new ObjectType([
        'name' => 'MutationRoot',
        'fields' => $mutation,
      ]);
    }

    return parent::__construct($config);
  }

  /**
   * {@inheritdoc}
   */
  public function build(SchemaConfig $config) {
    $query = $config->getQuery();

    // Add all fields to a field on the root query object (recursive). This is
    // required to enable adding a recursive reference to the query root for use
    // in a React & Relay setting.
    //
    // @see https://github.com/facebook/relay/issues/112#issuecomment-170648934
    $query->addField('root', [
      'type' => new NonNullType($query),
      'resolve' => [get_class($this), 'resolveRoot'],
    ]);
  }

  /**
   * Dummy resolve function.
   *
   * Used to enable adding a recursive reference to the query root for use in
   * a React & Relay setting.
   *
   * @see https://github.com/facebook/relay/issues/112#issuecomment-170648934
   */
  public static function resolveRoot() {
    return [];
  }
}
