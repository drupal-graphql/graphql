<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\Entity\ServerInterface;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\ComposableSchema;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql\Plugin\SchemaExtensionPluginManager;
use Drupal\graphql\Plugin\SchemaPluginManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests that the server configuration can be retrieved.
 *
 * @group graphql
 */
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
   * Test servers.
   *
   * @var \Drupal\graphql\Entity\ServerInterface[]
   */
  protected array $servers;

  /**
   * Mocked schema extension plugins.
   *
   * @var \Drupal\graphql\Plugin\SchemaExtensionPluginInterface[]
   */
  protected array $extensionPlugins;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $serverStorage = $this->container->get('entity_type.manager')->getStorage('graphql_server');

    // Create two test servers which are using the same schema plugin
    // (ComposableSchema) but configured with different extensions. This should
    // result in a different schema for each server.
    foreach (self::TEST_SCHEMAS as $id => $schema) {
      $this->extensionPlugins[$id] = $this->mockSchemaExtensionPlugin($id, $schema);

      $schemaConfiguration = ['composable' => ['extensions' => [$id => $id]]];
      $this->createTestServer('composable', $id, ['schema_configuration' => $schemaConfiguration]);
      $servers = $serverStorage->loadByProperties(['endpoint' => $id]);
      $server = reset($servers);
      if ($server instanceof ServerInterface) {
        $this->servers[$id] = $server;
      }
    }

    // Replace the schema plugin manager with a mocked version that returns the
    // requested mocked schema extension plugins.
    $schemaPluginManager = $this->mockPluginManager(array_keys(self::TEST_SCHEMAS));
    // @phpstan-ignore-next-line PHPStan doesn't find this method on the parent.
    $schemaPluginManager->method('createInstance')
      ->willReturnCallback(fn ($pluginId, $pluginConfig) => match ([$pluginId, $pluginConfig['server_id']]) {
        ['composable', $this->servers['node']->id()] => $this->extensionPlugins['node'],
        ['composable', $this->servers['term']->id()] => $this->extensionPlugins['term'],
        default => NULL,
      });

    $this->container->set('plugin.manager.graphql.schema', $schemaPluginManager);
  }

  /**
   * Tests the retrieval of a server's configuration.
   *
   * When multiple servers are created, each server should return its own
   * configuration. This checks that the configured schema is returned for each
   * of the two test servers.
   */
  public function testRetrieveServerConfiguration(): void {
    /** @var \Drupal\graphql\Entity\Server $server */
    foreach ($this->servers as $server) {
      $endpoint = $server->endpoint;
      $expected_schema = self::TEST_SCHEMAS[$endpoint];
      $actual_schema = $this->getPrintedSchema($server);
      // The printed schema might be formatted differently. Strip whitespace.
      $this->assertEquals(preg_replace('/\s+/', '', $expected_schema), preg_replace('/\s+/', '', $actual_schema));
    }
  }

  /**
   * Returns a mocked schema extension plugin.
   *
   * @param string $id
   *   The ID to use for the schema extension plugin.
   * @param string $schema
   *   The schema to associate with the schema extension plugin.
   */
  protected function mockSchemaExtensionPlugin(string $id, string $schema): ComposableSchema {
    $extensionManager = $this->getMockBuilder(SchemaExtensionPluginManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['createInstance'])
      ->getMock();

    $schemaExtensionPlugin = $this->getMockBuilder(SdlSchemaExtensionPluginBase::class)
      ->disableOriginalConstructor()
      ->getMock();

    $extensionManager->expects($this->any())
      ->method('createInstance')
      ->with($id)
      ->willReturn($schemaExtensionPlugin);

    $mock = $this->getMockBuilder(ComposableSchema::class)
      ->setConstructorArgs([
        [],
        'composable',
        [],
        $this->container->get('cache.graphql.ast'),
        $this->container->get('module_handler'),
        $extensionManager,
        ['development' => FALSE],
      ])
      ->onlyMethods(['getSchemaDefinition', 'getResolverRegistry'])
      ->getMock();

    $mock->expects(static::any())
      ->method('getSchemaDefinition')
      ->willReturn($schema);

    $registry = new ResolverRegistry();
    $mock->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    return $mock;
  }

  /**
   * Returns a mocked schema plugin manager.
   *
   * @param string[] $ids
   *   The IDs of the schema plugins that the manager should return.
   */
  protected function mockPluginManager(array $ids): SchemaPluginManager|MockObject {
    $schemaPluginManager = $this->getMockBuilder(SchemaPluginManager::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getDefinitions', 'createInstance'])
      ->getMock();

    $definitions = array_map(static fn ($id) => [
      'id' => $id,
      'name' => 'Composable schema',
      'provider' => 'graphql',
      'class' => ComposableSchema::class,
    ], $ids);

    $schemaPluginManager->expects($this->any())
      ->method('getDefinitions')
      ->willReturn($definitions);

    return $schemaPluginManager;
  }

}
