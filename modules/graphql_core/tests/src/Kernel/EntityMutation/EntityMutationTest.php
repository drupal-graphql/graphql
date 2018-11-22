<?php

namespace Drupal\Tests\graphql_core\Kernel\EntityMutation;

use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\CreateEntityBase;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\DeleteEntityBase;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\UpdateEntityBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Test abstract entity mutation classes.
 *
 * @group graphql_core
 */
class EntityMutationTest extends GraphQLContentTestBase {

  /**
   * {@inheritdoc}
   *
   * Allow to modify all nodes.
   */
  protected function userPermissions() {
    $perms = parent::userPermissions();
    $perms[] = 'bypass node access';
    $perms[] = 'administer nodes';
    return $perms;
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->mockInputType('node_input', [
      'name' => 'NodeInput',
      'fields' => [
        'title' => 'String',
        'body' => 'String',
      ],
    ]);
  }

  protected function mockMutationFactory($definition, $result = NULL, $builder = NULL) {
    $mutation = $this->getMockBuilder([
      'createNode' => CreateEntityBase::class,
      'updateNode' => UpdateEntityBase::class,
      'deleteNode' => DeleteEntityBase::class,
    ][$definition['id']])
      ->setConstructorArgs([
        [],
        $definition['id'],
        $definition,
        $this->container->get('entity_type.manager'),
        $this->container->get('renderer'),
      ])
      ->setMethods([
        'extractEntityInput',
      ])->getMock();

    if (isset($result)) {
      $mutation
        ->expects(static::any())
        ->method('extractEntityInput')
        ->with(static::anything(), static::anything(), static::anything(), static::anything())
        ->will($this->toBoundPromise($result, $mutation));
    }

    if (is_callable($builder)) {
      $builder($mutation);
    }

    return $mutation;
  }

  /**
   * Test entity creation.
   */
  public function testCreateEntityMutation() {
    $this->mockMutation('createNode', [
      'name' => 'createNode',
      'entity_type' => 'node',
      'entity_bundle' => 'test',
      'arguments' => [
        'input' => 'NodeInput',
      ],
      'type' => 'EntityCrudOutput',
    ], function ($source, $args, $context, $info) {
        return [
          'title' => $args['input']['title'],
          'status' => 1,
          'body' => [
            'value' => $args['input']['body'],
          ],
        ];
    });

    $this->assertResults('mutation ($node: NodeInput!) { createNode(input: $node) { entity { entityId } } }', [
      'node' => [
        'title' => 'Test',
        'body' => 'This is a test.',
      ],
    ], [
      'createNode' => [
        'entity' => [
          'entityId' => 1,
        ],
      ],
    ], $this->defaultMutationCacheMetaData());

    $this->assertEquals('Test', Node::load(1)->getTitle());
  }

  /**
   * Test entity creation violations.
   */
  public function testCreateEntityMutationViolation() {
    $this->mockMutation('createNode', [
      'name' => 'createNode',
      'entity_type' => 'node',
      'entity_bundle' => 'test',
      'arguments' => [
        'input' => 'NodeInput',
      ],
      'type' => 'EntityCrudOutput',
    ], function ($source, $args, $context, $info) {
      return [
        'status' => 1,
        'body' => [
          'value' => $args['input']['body'],
        ],
      ];
    });

    $this->assertResults('mutation ($node: NodeInput!) { createNode(input: $node) { violations { message path } } }', [
      'node' => [
        'title' => 'Test',
        'body' => 'This is a test.',
      ],
    ], [
      'createNode' => [
        'violations' => [
          0 => [
            'message' => 'This value should not be null.',
            'path' => 'title',
          ],
        ],
      ],
    ], $this->defaultMutationCacheMetaData());

    $this->assertEmpty(Node::load(1));
  }

  /**
   * Test entity updates.
   */
  public function testUpdateEntityMutation() {
    $this->mockMutation('updateNode', [
      'name' => 'updateNode',
      'entity_type' => 'node',
      'entity_bundle' => 'test',
      'arguments' => [
        'id' => 'String',
        'input' => 'NodeInput',
      ],
      'type' => 'EntityCrudOutput',
    ], function ($source, $args, $context, $info) {
      return [
        'title' => $args['input']['title'],
      ];
    });

    $this->createNode([
      'title' => 'Old title',
      'status' => 1,
      'type' => 'test',
      'body' => [
        'value' => 'Old body',
      ],
    ]);

    $this->assertResults('mutation ($node: NodeInput!, $nid: String!) { updateNode(id: $nid, input: $node) { entity { entityLabel } } }', [
      'nid' => '1',
      'node' => [
        'title' => 'Test',
      ],
    ], [
      'updateNode' => [
        'entity' => [
          'entityLabel' => 'Test',
        ],
      ],
    ], $this->defaultMutationCacheMetaData());

    $this->assertEquals('Test', Node::load(1)->getTitle());
  }

  /**
   * Test entity deletion.
   */
  public function testDeleteEntityMutation() {
    $this->mockMutation('deleteNode', [
      'name' => 'deleteNode',
      'entity_type' => 'node',
      'entity_bundle' => 'test',
      'arguments' => [
        'id' => 'String',
      ],
      'type' => 'EntityCrudOutput',
    ]);

    $this->createNode([
      'title' => 'Test',
      'status' => 1,
      'type' => 'test',
    ]);

    $this->assertResults('mutation ($nid: String!) { deleteNode(id: $nid) { entity { entityLabel } } }', [
      'nid' => '1',
    ], [
      'deleteNode' => [
        'entity' => [
          'entityLabel' => 'Test',
        ],
      ],
    ], $this->defaultMutationCacheMetaData());

    $this->assertEmpty(Node::load(1));
  }

}
