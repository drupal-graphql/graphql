<?php

namespace Drupal\Tests\graphql_views\Kernel;

use Drupal\graphql\GraphQL\TypeCollector;

/**
 * Test contextual views support in GraphQL.
 *
 * @group graphql_views
 */
class ContextualViewsTest extends ViewsTestBase {

  /**
   * The GraphQL schema.
   *
   * @var \Youshido\GraphQL\Schema\AbstractSchema
   */
  protected $schema;

  /**
   * The types collected from the GraphQL schema.
   *
   * @var \Youshido\GraphQL\Type\TypeInterface[]
   */
  protected $types;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->createContentType(['type' => 'test2']);
    $this->schema = \Drupal::service('graphql.schema');
    $this->types = TypeCollector::collectTypes($this->schema);
  }

  /**
   * Test if view contextual filters are set properly.
   */
  public function testContextualViewArgs() {
    $test2Node = $this->createNode(['type' => 'test2']);
    $this->executeQueryFile('contextual.gql', [
      'test2NodeId' => $test2Node->id(),
    ]);

    $this->assertEquals(drupal_static('graphql_views_test:view:args'), [
      'graphql_test:contextual_title_arg' => [
        0 => [NULL],
        1 => ['X'],
      ],
      'graphql_test:contextual_node' => [
        0 => [NULL],
        1 => ['X'],
        2 => ['1'],
        3 => ['X'],
        4 => ['1'],
        5 => ['X'],
      ],
      'graphql_test:contextual_nodetest' => [
        0 => [NULL],
        1 => ['X'],
        2 => ['1'],
        3 => ['X'],
      ],
      'graphql_test:contextual_node_and_nodetest' => [
        0 => [NULL, NULL],
        1 => ['X', 'X'],
        2 => [$test2Node->id(), NULL],
        3 => ['X', 'X'],
        4 => ['1', '1'],
        5 => ['X', 'X'],
      ],
    ]);
  }

  /**
   * Test if view fields are attached to correct types.
   */
  public function testContextualViewFields() {
    $field = 'graphqlTestContextualTitleArgView';
    $this->assertFieldExists('Root', $field);
    $this->assertFieldNotExists('Node', $field);
    $this->assertFieldNotExists('NodeTest', $field);

    $field = 'graphqlTestContextualNodeView';
    $this->assertFieldExists('Root', $field);
    $this->assertFieldExists('Node', $field);
    $this->assertFieldExists('NodeTest', $field);

    $field = 'graphqlTestContextualNodetestView';
    $this->assertFieldExists('Root', $field);
    $this->assertFieldNotExists('Node', $field);
    $this->assertFieldExists('NodeTest', $field);

    $field = 'graphqlTestContextualNodeAndNodetestView';
    $this->assertFieldExists('Root', $field);
    $this->assertFieldExists('Node', $field);
    $this->assertFieldExists('NodeTest', $field);
  }

  /**
   * Assert that field exists on a GraphQL type.
   *
   * @param string $type
   *   GraphQL type name.
   * @param string $fieldName
   *   GraphQL field name.
   */
  protected function assertFieldExists($type, $fieldName) {
    $this->assertArrayHasKey($fieldName, $this->getFields($type), "Field {$fieldName} exists on {$type} type.");
  }

  /**
   * Assert that field does not exist on a GraphQL type.
   *
   * @param string $type
   *   GraphQL type name.
   * @param string $fieldName
   *   GraphQL field name.
   */
  protected function assertFieldNotExists($type, $fieldName) {
    $this->assertArrayNotHasKey($fieldName, $this->getFields($type), "Field {$fieldName} does not exist on {$type} type.");
  }

  /**
   * Returns list of GraphQL fields attached to a type.
   *
   * @param string $type
   *   GraphQL type name.
   *
   * @return \Youshido\GraphQL\Field\Field[]
   */
  protected function getFields($type) {
    return $type === 'Root'
      ? $this->schema->getQueryType()->getFields()
      : $this->types[$type]->getConfig()->get('fields');
  }

}
