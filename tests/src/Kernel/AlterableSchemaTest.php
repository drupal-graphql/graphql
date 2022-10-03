<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Tests\graphql\Kernel\Schema\AlterableComposableTestSchema;

/**
 * Tests the alterable schema.
 *
 * @group graphql
 */
class AlterableSchemaTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'graphql_alterable_schema_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        alterableQuery(data: AlterableArgument): String
      }
      type AlterableArgument {
        id: Int
      }
    GQL;

    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'alterableQuery');
  }

  /**
   * Test if invariant violation errors are logged.
   */
  public function testSchemaAlteredQueryArgumentToRequired(): void {
    $result = $this->query('query { alterableQuery }');
    $this->assertSame(200, $result->getStatusCode());
    // The should be error that query ID is required.
    $this->assertSame([
      'errors' => [
        0 => [
          'message' => 'Field "alterableQuery" argument "data" of type "AlterableArgument!" is required but not provided.',
          'extensions' => [
            'category' => 'graphql',
          ],
          'locations' => [
            0 => [
              'line' => 1,
              'column' => 9,
            ],
          ],
        ],
      ],
    ], json_decode($result->getContent(), TRUE));
  }

  /**
   * {@inheritdoc}
   */
  protected function mockSchema($id, $schema, array $extensions = []): void {
    /** @var \PHPUnit\Framework\MockObject\MockObject $extensionManager */
    $extensionManager = $this->getMockBuilder(SchemaExtensionPluginManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getExtensions'])
      ->getMock();

    $extensionManager->expects(static::any())
      ->method('getExtensions')
      ->willReturn($extensions);

    // Replace mock schema with out own implementation.
    $this->schema = $this->getMockBuilder(AlterableComposableTestSchema::class)
      ->setConstructorArgs([
        [],
        $id,
        [],
        $this->container->get('cache.graphql.ast'),
        $this->container->get('module_handler'),
        $extensionManager,
        ['development' => FALSE],
        $this->container->get('event_dispatcher'),
      ])
      ->onlyMethods(['getSchemaDefinition', 'getResolverRegistry'])
      ->getMockForAbstractClass();

    $this->schema->expects(static::any())
      ->method('getSchemaDefinition')
      ->willReturn($schema);

    $this->registry = new ResolverRegistry();
    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($this->registry);
  }

}
