<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use GraphQL\Utils\SchemaPrinter;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests that composable schemas load each server's configured extensions.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class ServerConfigurationTest extends GraphQLTestBase {

  const TEST_SCHEMAS = [
    'node' => <<<GQL
      type Node {
        title: String!
      }
      type NodeQuery {
        node(uuid: String): Node
      }
    GQL,
    'term' => <<<GQL
      type Term {
        label: String!
      }
      type TermQuery {
        term(uuid: String): Term
      }
    GQL,
  ];

  /**
   * Test servers keyed by their selected extension IDs.
   *
   * @var array<string, \Drupal\graphql\Entity\ServerInterface>
   */
  protected array $servers = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $extensions = [];
    foreach (self::TEST_SCHEMAS as $id => $definition) {
      $extensions[$id] = $this->mockSchemaExtension(
        $id,
        $definition,
        "extend type Query { $id(uuid: String): " . ucfirst($id) . ' }',
      );
    }

    // Mock only extension instantiation. Schema discovery, configuration,
    // composition and caching use their real implementations.
    $extensionManager = $this->getMockBuilder(SchemaExtensionPluginManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['createInstance'])
      ->getMock();
    $extensionManager->method('createInstance')
      ->willReturnCallback(static fn (string $id) => $extensions[$id]);
    $this->container->set('plugin.manager.graphql.schema_extension', $extensionManager);

    foreach (array_keys(self::TEST_SCHEMAS) as $id) {
      $server = $this->createTestServer('composable', $id, [
        'schema_configuration' => [
          'composable' => ['extensions' => [$id => $id]],
        ],
      ]);
      $this->servers[$id] = $server;
    }
  }

  /**
   * Tests that each server's schema contains only its selected extension.
   */
  public function testRetrieveServerConfiguration(): void {
    // Build both schemas twice to exercise the shared AST cache as well.
    for ($pass = 0; $pass < 2; $pass++) {
      foreach ($this->servers as $id => $server) {
        $schema = $server->configuration()->getSchema();
        $this->assertNotNull($schema);
        $schema->assertValid();
        $type = ucfirst($id);
        $expected = <<<GQL
          type Schema {
            query: Query
          }
          type Query {
            $id(uuid: String): $type
          }
        GQL;
        $expected .= self::TEST_SCHEMAS[$id];
        $actual = SchemaPrinter::doPrint($schema);
        // Ignore formatting while comparing the complete composed schema.
        $this->assertSame(
          preg_replace('/\\s+/', '', $expected),
          preg_replace('/\\s+/', '', $actual),
        );
      }
    }
  }

}
